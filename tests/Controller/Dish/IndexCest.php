<?php

declare(strict_types=1);


namespace App\Tests\Controller\Dish;

use App\Factory\DishFactory;
use App\Tests\Support\ControllerTester;

final class IndexCest
{
    public function contactListContainsRightNumberOfDishes(ControllerTester $I): void
    {
        DishFactory::createMany(5);
        $I->amOnPage('/contact');
        $I->seeResponseCodeIsSuccessful();
        $I->seeInTitle('Liste des contacts');
        $I->see('Liste des contacts', 'h1');
        $I->seeElement('ul.contacts');
        $I->seeNumberOfElements('ul.contacts > li', 5);
    }

}
