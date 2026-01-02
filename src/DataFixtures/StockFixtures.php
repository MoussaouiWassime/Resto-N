<?php

namespace App\DataFixtures;

use App\Factory\ProductFactory;
use App\Factory\RestaurantFactory;
use App\Factory\StockFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StockFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $restaurants = RestaurantFactory::all();

        foreach ($restaurants as $restaurant) {
            $products = ProductFactory::randomSet(3);

            foreach ($products as $product) {
                StockFactory::createOne([
                    'restaurant' => $restaurant,
                    'product' => $product,
                ]);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
            ProductFixtures::class,
        ];
    }
}
