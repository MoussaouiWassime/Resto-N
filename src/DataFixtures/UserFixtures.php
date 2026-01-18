<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'firstname' => 'Damien',
            'lastname' => 'Ho',
            'email' => 'ho@example.com',
            'roles' => ['ROLE_ADMIN'],
        ]);

        UserFactory::createOne([
            'firstname' => 'Wassime',
            'lastname' => 'Moussaoui',
            'email' => 'moussaoui@example.com',
            'roles' => ['ROLE_ADMIN'],
        ]);

        UserFactory::createOne([
            'firstname' => 'Jérôme',
            'lastname' => 'Cutrona',
            'email' => 'cutrona@example.com',
            'roles' => ['ROLE_ADMIN'],
        ]);

        UserFactory::createMany(10);
    }
}
