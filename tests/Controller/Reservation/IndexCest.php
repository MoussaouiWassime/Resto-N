<?php

declare(strict_types=1);

namespace App\Tests\Controller\Reservation;

use App\Factory\ReservationFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RestaurantTableFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class IndexCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $I->amOnPage('/reservation');

        $I->seeCurrentRouteIs('app_login');
    }

    public function pageShowsUserReservations(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);

        $restaurant = RestaurantFactory::createOne(['name' => 'La Bonne Fourchette']);
        $table = RestaurantTableFactory::createOne(['number' => 12, 'restaurant' => $restaurant]);

        ReservationFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $user,
            'restaurantTable' => $table,
            'numberOfPeople' => 2,
            'reservationDate' => new \DateTime('+1 day 12:00'),
            'status' => 'Confirmée',
        ]);

        $I->amOnPage('/reservation/');

        $I->seeResponseCodeIs(200);

        $I->see('La Bonne Fourchette');
        $I->see('2 pers.');
        $I->see('Table 12');

        $I->see('Modifier', 'a');
        $I->see('Annuler', 'a');
    }
}
