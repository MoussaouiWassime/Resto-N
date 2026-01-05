<?php

declare(strict_types=1);

namespace App\Tests\Controller\Restaurant;

use App\Factory\DishFactory;
use App\Factory\RestaurantFactory;
use App\Tests\Support\ControllerTester;

final class ShowCest
{
    public function restaurantPageContainsRightNumberDishCategories(ControllerTester $I): void
    {
        $dishes = DishFactory::createMany(10);
        RestaurantFactory::createOne(['name' => 'Aaaaamanda', 'dishes' => $dishes]);
        $I->amOnPage('/restaurant');
        $I->click('Aaaaamanda');
        $I->seeResponseCodeIsSuccessful();
        $I->see('Aaaaamanda', 'h1');
        $I->seeElement('nav.filtres-dish');
        $I->seeNumberOfElements('nav.filtres-dish > a', 4);
    }

    public function restaurantPageContainsDescriptionAdressAndAction(ControllerTester $I): void
    {
        $dishes = DishFactory::createMany(10);
        RestaurantFactory::createOne(['name' => 'Aaaaamanda', 'dishes' => $dishes]);
        $I->amOnPage('/restaurant');
        $I->click('Aaaaamanda');
        $I->amOnPage('/restaurant/1');
        $I->seeResponseCodeIsSuccessful();
        $I->seeElement('span.description');
        $I->seeElement('span.address');
        $I->seeElement('nav.action-restaurant');
        $I->seeElement('a.btn');
    }
}
