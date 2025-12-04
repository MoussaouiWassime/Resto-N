<?php

namespace App\DataFixtures;

use App\Factory\ProductCategoryFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $json = file_get_contents(__DIR__.'/../../data/ProductCategory.json');
        $categories = json_decode($json, true);

        foreach ($categories as $category) {
            ProductCategoryFactory::createOne($category);
        }
    }
}
