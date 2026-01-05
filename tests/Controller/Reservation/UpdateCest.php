<?php

declare(strict_types=1);

namespace App\Tests\Controller\Reservation;

use App\Factory\ReservationFactory;
use App\Factory\RestaurantFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class UpdateCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        RestaurantFactory::createOne();
        $reservation = ReservationFactory::createOne();

        $I->amOnPage('/reservation/update/'.$reservation->getId());

        $I->seeCurrentRouteIs('app_login');
    }

    public function formShowsDataBeforeUpdating(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);
        $reservation = ReservationFactory::createOne([
            'user' => $user,
            'numberOfPeople' => 5,
            'restaurant' => RestaurantFactory::createOne(),
        ]);
        $I->amOnPage('/reservation/update/'.$reservation->getId());
        $I->seeResponseCodeIs(200);
        $I->seeInTitle('Modifier la réservation');
        $I->see('Mettre à jour', 'button');
        $I->seeInField('reservation[numberOfPeople]', 5);
    }
}
