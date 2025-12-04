<?php

namespace App\DataFixtures;

use App\Factory\ReservationFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        ReservationFactory::createMany(50);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            RestaurantFixtures::class,
        ];
    }
}
