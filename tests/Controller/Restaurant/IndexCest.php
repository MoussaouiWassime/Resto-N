<?php

declare(strict_types=1);

namespace App\Tests\Controller\Restaurant;

use App\Factory\RestaurantFactory;
use App\Tests\Support\ControllerTester;

final class IndexCest
{
    public function restaurantListContainsRightNumberOfRestaurants(ControllerTester $I): void
    {
        RestaurantFactory::createMany(10);
        $I->amOnPage('/restaurant');
        $I->seeResponseCodeIsSuccessful();
        $I->see('Liste des restaurants ', 'h1');
        $I->seeElement('ul.list-group');
        $I->seeNumberOfElements('ul.list-group > div > li', 10);
    }

    public function linkIsFirstRestaurant(ControllerTester $I): void
    {
        $pizzeria = RestaurantFactory::createOne(['name' => 'Aaaaamanda']);
        RestaurantFactory::createMany(5);
        $I->amOnPage('/restaurant');
        $I->click('Aaaaamanda');
        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentRouteIs('app_restaurant_show', ['id' => $pizzeria->getId()]);
    }
}
