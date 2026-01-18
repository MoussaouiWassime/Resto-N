<?php

declare(strict_types=1);

namespace App\Tests\Controller\Restaurant;

use App\Factory\DishFactory;
use App\Factory\RestaurantFactory;
use App\Tests\Support\ControllerTester;

final class ShowCest
{
    public function restaurantPageShowsDishes(ControllerTester $I): void
    {
        $restaurant = RestaurantFactory::createOne(['name' => 'Resto avec Plats']);
        DishFactory::createMany(3, ['restaurant' => $restaurant]);

        $I->amOnPage('/restaurant/' . $restaurant->getId());

        $I->seeResponseCodeIsSuccessful();
        $I->see('Notre Carte', 'h2');
        $I->seeElement('.card');
    }

    public function restaurantPageContainsDescriptionAdressAndAction(ControllerTester $I): void
    {
        $restaurant = RestaurantFactory::createOne([
            'name' => 'Aaaaamanda',
            'description' => 'Une super description de test',
            'address' => '10 Rue de la Paix',
            'city' => 'Paris',
            'postalCode' => '75000',
        ]);

        $I->amOnPage('/restaurant/' . $restaurant->getId());

        $I->seeResponseCodeIsSuccessful();

        $I->see('Aaaaamanda', 'h1');
        $I->see('Une super description de test');
        $I->see('10 Rue de la Paix');
        $I->see('Paris');

        $I->see('Commander', 'a');
        $I->see('Réserver une table', 'a');
    }
}
