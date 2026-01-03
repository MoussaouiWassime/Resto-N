<?php

declare(strict_types=1);

namespace App\Tests\Controller\Reservation;

use App\Entity\Reservation;
use App\Factory\ReservationFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RestaurantTableFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class DeleteCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        RestaurantFactory::createOne();
        $reservation = ReservationFactory::createOne();

        $I->amOnPage('/reservation/delete/'.$reservation->getId());

        $I->seeCurrentRouteIs('app_login');
    }

    public function pageShowsConfirmationDetails(ControllerTester $I): void
    {
        $user = UserFactory::createOne(['email' => 'test@test.com'])->_real();
        $I->amLoggedInAs($user);

        $restaurant = RestaurantFactory::createOne();
        $table = RestaurantTableFactory::createOne(['number' => 99, 'restaurant' => $restaurant]);

        $reservation = ReservationFactory::createOne([
            'user' => $user,
            'restaurant' => $restaurant,
            'restaurantTable' => $table,
            'numberOfPeople' => 4,
        ]);

        $I->amOnPage('/reservation/delete/'.$reservation->getId());
        $I->see('Êtes-vous sûr de vouloir supprimer cette réservation ?', 'h3');
        $I->see('test@test.com');
        $I->see('Couverts :4 personnes');
        $I->see('N°99');
        $I->see('Supprimer', 'button');
        $I->see('Annuler', 'button');
    }

    public function canDeleteReservation(ControllerTester $I): void
    {
        RestaurantFactory::createOne();
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);
        $reservation = ReservationFactory::createOne(['user' => $user]);
        $reservationId = $reservation->getId();

        $I->amOnPage('/reservation/delete/'.$reservationId);
        $I->click('Supprimer');
        $I->dontSeeInRepository(Reservation::class, ['id' => $reservationId]);
        $I->seeCurrentRouteIs('app_reservation_index_restaurant', [
            'id' => $reservation->getRestaurant()->getId(),
        ]);
    }
}
