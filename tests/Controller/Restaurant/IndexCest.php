<?php

declare(strict_types=1);

namespace App\Tests\Controller\Restaurant;

use App\Factory\RestaurantFactory;
use App\Tests\Support\ControllerTester;

final class IndexCest
{
    public function linkIsFirstRestaurant(ControllerTester $I): void
    {
        $pizzeria = RestaurantFactory::createOne(['name' => 'Aaaaamanda']);
        RestaurantFactory::createMany(5);
        $I->amOnPage('/restaurant/'.$pizzeria->getId());
        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentRouteIs('app_restaurant_show', ['id' => $pizzeria->getId()]);
    }
}
