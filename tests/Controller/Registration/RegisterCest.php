<?php

declare(strict_types=1);

namespace App\Tests\Controller\Registration;

use App\Entity\User;
use App\Tests\Support\ControllerTester;

final class RegisterCest
{
    public function pageIsAccessible(ControllerTester $I): void
    {
        $I->amOnPage('/register');
        $I->seeResponseCodeIs(200);
        $I->see('Inscription', 'h1');
    }
}
