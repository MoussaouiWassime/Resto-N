<?php

namespace App\DataFixtures;

use App\Factory\ReviewFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ReviewFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ReviewFactory::createMany(30);
    }
}
