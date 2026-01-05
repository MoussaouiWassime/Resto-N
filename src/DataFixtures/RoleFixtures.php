<?php

namespace App\DataFixtures;

use App\Factory\RestaurantFactory;
use App\Factory\RoleFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $restaurants = RestaurantFactory::all();
        foreach ($restaurants as $restaurant) {
            RoleFactory::createOne([
                'restaurant' => $restaurant,
                'role' => 'P',
            ]);

            RoleFactory::createMany(3, [
                'restaurant' => $restaurant,
                'role' => 'S',
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
