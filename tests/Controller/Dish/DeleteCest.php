<?php

declare(strict_types=1);

namespace App\Tests\Controller\Dish;

use App\Entity\Dish;
use App\Factory\DishFactory;
use App\Factory\ReservationFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RoleFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class DeleteCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        RestaurantFactory::createOne();
        $reservation = ReservationFactory::createOne();
        $restaurant = RestaurantFactory::createOne(['name' => 'Chez Mario']);
        $dish = DishFactory::createOne(
            ['restaurant' => $restaurant],
        );
        $I->amOnPage("/dish/{$dish->getId()}/delete/");
        $I->seeCurrentRouteIs('app_login');
    }

    public function pageShowsConfirmationDetails(ControllerTester $I): void
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

        $I->amOnPage("/dish/{$dish->getId()}/delete/");
        $I->see('Êtes-vous sûr de vouloir supprimer votre plat ?');
        $I->see('Supprimer', 'button');
        $I->see('Annuler', 'button');
    }

    public function canDeleteReservation(ControllerTester $I): void
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
        $I->amOnPage("/dish/{$dish->getId()}/delete/");
        $I->click('Supprimer');
        $I->dontSeeInRepository(Dish::class, ['id' => $dish->getId()]);
        $I->seeCurrentRouteIs('app_restaurant_show', ['id' => $restaurant->getId()]);
    }
}
