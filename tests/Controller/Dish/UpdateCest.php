<?php

declare(strict_types=1);

namespace App\Tests\Controller\Dish;

use App\Factory\DishFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RoleFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class UpdateCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $restaurant = DishFactory::createOne();
        $I->amOnPage("/restaurant/{$restaurant->getId()}/dish/create/");
        $I->seeCurrentRouteIs('app_login');
    }

    public function formCreateforDishforRestaurant(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);
        $restaurant = RestaurantFactory::createOne(['name' => 'Chez Mario']);
        $role = RoleFactory::createOne([ // Cette variable n'a pas vocation à être utilisée, mais est necéssaire pour la vérification que l'utilisateur est propriétaire
            'role' => 'P',
            'restaurant' => $restaurant,
            'user' => $user,
        ]);
        $dish = DishFactory::createOne(
            ['restaurant' => $restaurant],
        );
        $I->amOnPage("/dish/{$dish->getId()}/update/");

        $I->seeResponseCodeIs(200);
        $I->see("Édition du plat : {$dish->getName()}", 'h1');

        $I->see('Name');
        $I->see('Description');
        $I->see('Price');
        $I->see('Photo du plat');
        $I->see('Category');

        $I->seeElement('form');
        $I->see('Éditer le plat', 'button');
    }
}
