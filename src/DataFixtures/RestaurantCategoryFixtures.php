<?php

namespace App\DataFixtures;

use App\Factory\RestaurantCategoryFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RestaurantCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $json = file_get_contents(__DIR__.'/../../data/RestaurantCategory.json');
        $categories = json_decode($json, true);

        foreach ($categories as $category) {
            RestaurantCategoryFactory::createOne($category);
        }
    }
}
