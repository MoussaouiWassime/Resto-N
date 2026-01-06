<?php

declare(strict_types=1);

namespace App\Tests\Controller\Profile;

use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class ShowCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $I->amOnPage('/profile');
        $I->seeCurrentRouteIs('app_login');
    }

    public function pageShowsUserProfile(ControllerTester $I): void
    {
        $user = UserFactory::createOne([
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'jean.dupont@test.com',
            'phone' => '0601020304',
        ])->_real();

        $I->amLoggedInAs($user);
        $I->amOnPage('/profile');

        $I->seeResponseCodeIs(200);
        $I->see('Profil de Jean, Dupont', 'h1');
        $I->see('Nom : Jean, Dupont');
        $I->see('Email : jean.dupont@test.com');
        $I->see('Tel : 0601020304');

        $I->seeLink('Modifier mes informations', '/profile/update');
        $I->seeLink('Déconnexion', '/logout');
        $I->seeLink('Supprimer mon compte', '/profile/delete');

        $I->seeLink('Historique des réservations', '/reservation');
        $I->seeLink('Historique des commandes', '/order');
    }
}
