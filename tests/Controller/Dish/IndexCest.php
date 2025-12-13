<?php

declare(strict_types=1);

namespace App\Tests\Controller\Dish;

use App\Factory\DishFactory;
use App\Tests\Support\ControllerTester;

final class IndexCest
{
    public function dishListContainsRightNumberOfDishes(ControllerTester $I): void
    {
        DishFactory::createMany(5);
        $I->amOnPage('/dish');
        $I->seeResponseCodeIsSuccessful();
        $I->seeInTitle('Liste des Plats');
        $I->see('Liste des Plats', 'h1');
        $I->seeElement('ul.dish');
        $I->seeNumberOfElements('ul.dish > li', 5);
    }

    public function linkIsFirstDish(ControllerTester $I): void
    {
        $pizza = DishFactory::createOne(['name' => 'Pizza', 'description' => 'Une bonne pizza miam']);
        DishFactory::createMany(5);
        $I->amOnPage('/dish');
        $I->click('Pizza, Une bonne pizza miam');
        $I->seeResponseCodeIsSuccessful();
        $I->seeCurrentRouteIs('app_dish_show', ['id' => $pizza->getId()]);
    }
}
