<?php

namespace App\DataFixtures;

use App\Factory\RestaurantCategoryFactory;
use App\Factory\RestaurantFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RestaurantFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        RestaurantFactory::createMany(10, fn () => [
            'categories' => RestaurantCategoryFactory::randomRange(1, 3),
        ]);
    }
}
