<?php

declare(strict_types=1);

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Factory\ReservationFactory;
use App\Factory\UserFactory;
use App\Tests\Support\ControllerTester;

final class DeleteCest
{
    public function accessIsRestrictedToAuthenticatedUsers(ControllerTester $I): void
    {
        $I->amOnPage('/profile/delete');
        $I->seeCurrentRouteIs('app_login');
    }

    public function pageShowsConfirmationDetails(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();
        $I->amLoggedInAs($user);
        $I->amOnPage('/profile/delete');

        $I->seeResponseCodeIs(200);
        $I->see('Supprimer mon compte', 'h1');
        $I->see('Êtes-vous sûr de vouloir supprimer votre compte ?');

        $I->see('Supprimer', 'button');
        $I->see('Annuler', 'button');
    }

    public function canDeleteAccountWithDependencies(ControllerTester $I): void
    {
        $user = UserFactory::createOne()->_real();

        if (class_exists(ReservationFactory::class)) {
            ReservationFactory::createOne(['user' => $user]);
        }

        $userId = $user->getId();
        $I->amLoggedInAs($user);

        $I->amOnPage('/profile/delete');

        $I->click('Supprimer');

        $I->seeCurrentRouteIs('app_login');
        $I->dontSeeInRepository(User::class, ['id' => $userId]);
    }
}
