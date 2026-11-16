<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance\SessionEviction;

use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_session_eviction
 */
final class RememberMeSessionEvictionCest extends SessionEvictionBaseCest
{
    public function rememberMeSessionIsLoggedOutAfterPasswordChange(AcceptanceTester $I): void
    {
        $I->wantToTest('a remember-me session is logged out after the password changes');
        $this->setSessionEvictionState(true);

        $this->loginWithRememberMeCookieOnly($I);
        $this->seeLoggedIn($I);

        $this->changePasswordElsewhere($I);

        $this->seeLoggedOut($I);
    }

    public function rememberMeSessionStaysLoggedInWhilePasswordUnchanged(AcceptanceTester $I): void
    {
        $I->wantToTest('a remember-me session stays logged in while the password is unchanged');
        $this->setSessionEvictionState(true);

        $this->loginWithRememberMeCookieOnly($I);
        $this->seeLoggedIn($I);

        $this->seeLoggedIn($I);
    }

    public function rememberMeSessionSurvivesPasswordChangeWithFeatureOff(AcceptanceTester $I): void
    {
        $I->wantToTest('without the feature a remember-me session survives a password change (core behaviour)');
        $this->setSessionEvictionState(false);

        $this->loginWithRememberMeCookieOnly($I);
        $this->seeLoggedIn($I);

        $this->changePasswordElsewhere($I);

        $this->seeLoggedIn($I);
    }
}
