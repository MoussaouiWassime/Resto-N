<?php

namespace App\DataFixtures;

use App\Factory\RestaurantFactory;
use App\Factory\StatisticFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StatisticFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $restaurants = RestaurantFactory::all();

        foreach ($restaurants as $restaurant) {
            StatisticFactory::createMany(10, [
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
