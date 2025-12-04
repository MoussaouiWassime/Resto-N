<?php

namespace App\DataFixtures;

use App\Factory\DishFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemFactory;
use App\Factory\RestaurantFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $restaurants = RestaurantFactory::all();
        foreach ($restaurants as $restaurant) {
            $dishes = DishFactory::findBy(['restaurant' => $restaurant]);

            $orders = OrderFactory::createMany(5, [
                'restaurant' => $restaurant,
            ]);

            foreach ($orders as $order) {
                OrderItemFactory::createMany(rand(2, 4), function () use ($order, $dishes) {
                    return [
                        'order' => $order,
                        'dish' => $dishes[array_rand($dishes)],
                    ];
                });
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            RestaurantFixtures::class,
            DishFixtures::class,
        ];
    }
}
