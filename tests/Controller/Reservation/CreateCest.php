<?php

declare(strict_types=1);

namespace App\Tests\Controller\Reservation;

use App\Factory\RestaurantFactory;
use App\Tests\Support\ControllerTester;

final class CreateCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $restaurant = RestaurantFactory::createOne();

        $I->amOnPage('/reservation/create/'.$restaurant->getId());

        $I->seeCurrentRouteIs('app_login');
    }
}
