<?php

namespace App\DataFixtures;

use App\Factory\RestaurantCategoryFactory;
use App\Factory\RestaurantFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RestaurantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        RestaurantFactory::createMany(5, fn () => [
            'categories' => RestaurantCategoryFactory::randomRange(1, 3),
        ]);
    }

    public function getDependencies(): array
    {
        return [
            RestaurantCategoryFixtures::class,
        ];
    }
}
