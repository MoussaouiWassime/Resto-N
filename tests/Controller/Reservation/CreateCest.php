<?php

declare(strict_types=1);

namespace App\Tests\Controller\Reservation;

use App\Factory\RestaurantFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class CreateCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $restaurant = RestaurantFactory::createOne();

        $I->amOnPage('/reservation/create/'.$restaurant->getId());

        $I->seeCurrentRouteIs('app_login');
    }

    public function formShowsForNormalRestaurant(ControllerTester $I): void
    {
        // 1. Connexion
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);
        $restaurant = RestaurantFactory::createOne([
            'darkKitchen' => false,
            'name' => 'aaaa',
        ]);
        $I->amOnPage('/reservation/create/'.$restaurant->getId());
        $I->seeResponseCodeIs(200);
        $I->see("Horaires d'ouverture", 'h5');
        $I->see('Valider', 'button');
    }
}
