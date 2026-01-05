<?php

declare(strict_types=1);

namespace App\Tests\Controller\Reservation;

use App\Factory\ReservationFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RestaurantTableFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class ByRestaurantCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $restaurant = RestaurantFactory::createOne();

        $I->amOnPage('/reservation/restaurant/'.$restaurant->getId());

        $I->seeCurrentRouteIs('app_login');
    }

    public function pageListsReservationsForRestaurant(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);

        $restaurant = RestaurantFactory::createOne(['name' => 'La Bonne Fourchette']);

        $client = UserFactory::createOne(['firstName' => 'Jean', 'lastName' => 'Dupont']);
        $table = RestaurantTableFactory::createOne(['number' => 12, 'restaurant' => $restaurant]);

        ReservationFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $client,
            'restaurantTable' => $table,
            'numberOfPeople' => 2,
            'reservationDate' => new \DateTime('+1 day 12:00'),
            'status' => 'Confirmée',
        ]);

        ReservationFactory::createOne([
            'restaurant' => $restaurant,
            'user' => null,
            'restaurantTable' => null,
            'numberOfPeople' => 4,
            'reservationDate' => new \DateTime('+1 day 20:00'),
            'status' => 'En attente',
        ]);

        $I->amOnPage('/reservation/restaurant/'.$restaurant->getId());

        $I->seeResponseCodeIs(200);
        $I->see('Réservations : La Bonne Fourchette', 'h1');

        $I->see('Dupont Jean');
        $I->see('(2 pers.)');
        $I->see('Table 12');
        $I->see('Confirmée');

        $I->see('Client inconnu');
        $I->see('(4 pers.)');
        $I->see('En attente');

        $I->see('Modifier', 'a');
        $I->see('Supprimer', 'a');
    }
}
