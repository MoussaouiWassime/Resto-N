<?php

declare(strict_types=1);

namespace App\Tests\Controller\Home;

use App\Factory\RestaurantCategoryFactory;
use App\Factory\RestaurantFactory;
use App\Tests\Support\ControllerTester;

final class HomeCest
{
    public function homePageIsPublic(ControllerTester $I): void
    {
        $I->amOnPage('/');

        $I->seeResponseCodeIs(200);
        $I->seeInTitle("Accueil - Resto'N");
    }

    public function homePageListsCategoriesAndRestaurants(ControllerTester $I): void
    {
        $category = RestaurantCategoryFactory::createOne(['name' => 'Italien']);
        RestaurantFactory::createOne([
            'name' => 'La Pizza del Mama',
            'description' => 'Les meilleures pizzas',
            'categories' => [$category],
        ]);

        RestaurantCategoryFactory::createOne(['name' => 'Vegan']);

        $I->amOnPage('/');

        $I->see('Italien', 'h2');
        $I->see('La Pizza del Mama', 'h3');
        $I->see('Les meilleures pizzas');

        $I->see('Voir la carte');

        $I->see('Vegan', 'h2');
        $I->see('Aucun restaurant');
    }
}
