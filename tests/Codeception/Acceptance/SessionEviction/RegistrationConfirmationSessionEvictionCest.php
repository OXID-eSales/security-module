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
final class RegistrationConfirmationSessionEvictionCest extends SessionEvictionBaseCest
{
    public function confirmedUserStaysLoggedInWithStrictMode(AcceptanceTester $I): void
    {
        $I->wantToTest('a user who confirms the registration stays logged in with strict mode');
        $this->setSessionEvictionState(true, true);

        $this->confirmRegistration($I);

        $this->seeLoggedIn($I);
        $this->seeLoggedIn($I);
    }

    public function confirmationSessionIsLoggedOutAfterPasswordChange(AcceptanceTester $I): void
    {
        $I->wantToTest('a session opened by a registration confirmation is logged out after a password change');
        $this->setSessionEvictionState(true);

        $this->confirmRegistration($I);
        $this->seeLoggedIn($I);

        $this->changePasswordElsewhere($I);

        $this->seeLoggedOut($I);
    }

    private function confirmRegistration(AcceptanceTester $I): void
    {
        $I->amOnPage('/index.php?cl=register&fnc=confirmRegistration&uid=' . $this->createUpdateId($I));
        $I->waitForPageLoad();
    }
}
