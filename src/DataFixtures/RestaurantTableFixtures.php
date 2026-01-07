<?php

namespace App\DataFixtures;

use App\Factory\RestaurantFactory;
use App\Factory\RestaurantTableFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RestaurantTableFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $restaurants = RestaurantFactory::all();

        foreach ($restaurants as $restaurant) {
            RestaurantTableFactory::createMany(5, ['restaurant' => $restaurant]);
        }
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
        ];
    }
}
