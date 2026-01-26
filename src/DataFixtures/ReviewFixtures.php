<?php

namespace App\DataFixtures;

use App\Enum\RestaurantRole;
use App\Factory\RestaurantCategoryFactory;
use App\Factory\RestaurantFactory;
use App\Factory\ReviewFactory;
use App\Factory\RoleFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ReviewFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $restaurant = RestaurantFactory::createOne([
            'name' => 'Restaurant Cutrona',
            'description' => 'Le meilleur Restaurant de toute la France! Si vous cherchez
                une ambiance conviviale, couplée avec de bons petits plats, venez manger chez Cutrona!',
            'address' => 'Chemin du Roulier',
            'postal_code' => '51100',
            'city' => 'Reims',
            'image' => 'https://iut-info.univ-reims.fr/users/cutrona/intranet/css/images/jerome-cutrona.jpg',
            'categories' => RestaurantCategoryFactory::randomRange(1, 2),
        ]);

        $cutrona = UserFactory::findBy(['lastName' => 'Cutrona']);
        $damien = UserFactory::findBy(['lastName' => 'Ho']);
        $wassime = UserFactory::findBy(['lastName' => 'Moussaoui']);

        RoleFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $cutrona[0],
            'role' => RestaurantRole::OWNER,
        ]);
        RoleFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $damien[0],
            'role' => RestaurantRole::SERVER,
        ]);
        RoleFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $wassime[0],
            'role' => RestaurantRole::SERVER,
        ]);



        ReviewFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $damien[0],
            'rating' => 4,
            'comment' => "C'était très bon, je me suis régalé!",
        ]);
        ReviewFactory::createMany(30);
    }
}
