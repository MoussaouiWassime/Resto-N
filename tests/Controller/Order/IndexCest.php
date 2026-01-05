<?php

declare(strict_types=1);

namespace App\Tests\Controller\Order;

use App\Factory\DishFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemFactory;
use App\Factory\RestaurantFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class IndexCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $I->amOnPage('/order');
        $I->seeCurrentRouteIs('app_login');
    }

    public function pageShowsUserOrders(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);

        $restaurant = RestaurantFactory::createOne(['name' => 'Resto Test']);
        $dish = DishFactory::createOne(['name' => 'Pizza Test', 'price' => 1000, 'restaurant' => $restaurant]);

        $order = OrderFactory::createOne([
            'user' => $user,
            'restaurant' => $restaurant,
            'status' => 'C',
            'orderType' => 'S',
        ]);

        OrderItemFactory::createOne([
            'order' => $order,
            'dish' => $dish,
            'quantity' => 2,
        ]);

        $I->amOnPage('/order');

        $I->seeResponseCodeIs(200);
        $I->see('Mes Commandes', 'h1');
        $I->see('Resto Test');
        $I->see('Pizza Test');
        $I->see('20 €');
        $I->see('Sur place');
    }
}
