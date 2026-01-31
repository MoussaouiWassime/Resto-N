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
            'name' => 'Restaurant Gourmet',
            'description' => 'Le meilleur Restaurant de toute la France! Si vous cherchez
                une ambiance conviviale, couplée avec de bons petits plats, venez manger chez nous!',
            'address' => 'Chemin du Roulier',
            'postal_code' => '51100',
            'city' => 'Reims',
            'image' => 'https://example.com/restaurant.jpg',
            'categories' => RestaurantCategoryFactory::randomRange(1, 2),
        ]);

        $owner = UserFactory::createOne(['email' => 'owner@example.com']);
        $server = UserFactory::createOne(['email' => 'server@example.com']);
        $client = UserFactory::createOne(['email' => 'client@example.com']);

        RoleFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $owner,
            'role' => RestaurantRole::OWNER,
        ]);
        RoleFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $server,
            'role' => RestaurantRole::SERVER,
        ]);

        ReviewFactory::createOne([
            'restaurant' => $restaurant,
            'user' => $client,
            'rating' => 4,
            'comment' => "C'était très bon, je me suis régalé!",
        ]);
        ReviewFactory::createMany(30);
    }
}
