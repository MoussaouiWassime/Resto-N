<?php

declare(strict_types=1);

namespace App\Tests\Controller\Order;

use App\Factory\DishFactory;
use App\Factory\RestaurantFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class CreateCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $restaurant = RestaurantFactory::createOne();
        $I->amOnPage('/order/create/'.$restaurant->getId());
        $I->seeCurrentRouteIs('app_login');
    }

    public function formShowsDishesForRestaurant(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);
        $restaurant = RestaurantFactory::createOne(['name' => 'Chez Mario']);

        DishFactory::createOne([
            'name' => 'Pizza Margarita',
            'price' => 1200,
            'restaurant' => $restaurant,
        ]);
        DishFactory::createOne([
            'name' => 'Tiramisu',
            'price' => 650,
            'restaurant' => $restaurant,
        ]);

        $I->amOnPage('/order/create/'.$restaurant->getId());

        $I->seeResponseCodeIs(200);
        $I->see('Chez Mario', 'h1');

        $I->see('Pizza Margarita');
        $I->see('12 €');
        $I->see('Tiramisu');

        $I->seeElement('form');
        $I->see('Valider la commande', 'button');
    }
}
