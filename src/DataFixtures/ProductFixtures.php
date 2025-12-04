<?php

namespace App\DataFixtures;

use App\Factory\ProductFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $json = file_get_contents(__DIR__.'/../../data/Product.json');
        $products = json_decode($json, true);

        foreach ($products as $productData) {
            ProductFactory::createOne($productData);
        }
    }

    public function getDependencies(): array
    {
        return [
            ProductCategoryFixtures::class,
        ];
    }
}
