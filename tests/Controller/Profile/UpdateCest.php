<?php

declare(strict_types=1);

namespace App\Tests\Controller\Profile;

use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class UpdateCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $I->amOnPage('/profile/update');
        $I->seeCurrentRouteIs('app_login');
    }

    public function formShowsDataBeforeUpdating(ControllerTester $I): void
    {
        $user = UserFactory::createOne([
            'firstName' => 'Prenom',
            'lastName' => 'Nom',
            'email' => 'prenom@test.com',
        ])->_real();

        $I->amLoggedInAs($user);
        $I->amOnPage('/profile/update');

        $I->seeResponseCodeIs(200);
        $I->see('Modifier mon profil', 'h1');

        $I->seeInField('profile[firstName]', 'Prenom');
        $I->seeInField('profile[lastName]', 'Nom');
        $I->seeInField('profile[email]', 'prenom@test.com');
    }
}
