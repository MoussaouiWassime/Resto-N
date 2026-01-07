<?php

namespace App\DataFixtures;

use App\Factory\DishFactory;
use App\Factory\RestaurantFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DishFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $restaurants = RestaurantFactory::all();
        foreach ($restaurants as $restaurant) {
            DishFactory::createMany(5, [
                'restaurant' => $restaurant,
            ]);
        }
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
        ];
    }
}
