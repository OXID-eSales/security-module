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
final class StandardLoginSessionEvictionCest extends SessionEvictionBaseCest
{
    public function standardLoginIsLoggedOutAfterPasswordChangeWithFeatureOn(AcceptanceTester $I): void
    {
        $I->wantToTest('a standard login session is logged out after the password changes, feature on');
        $this->setSessionEvictionState(true);

        $this->assertLoggedOutAfterPasswordChange($I);
    }

    public function standardLoginIsLoggedOutAfterPasswordChangeWithFeatureOff(AcceptanceTester $I): void
    {
        $I->wantToTest('a standard login session is logged out after the password changes, feature off');
        $this->setSessionEvictionState(false);

        $this->assertLoggedOutAfterPasswordChange($I);
    }

    private function assertLoggedOutAfterPasswordChange(AcceptanceTester $I): void
    {
        $this->login($I);
        $this->seeLoggedIn($I);

        $this->changePasswordElsewhere($I);

        $this->seeLoggedOut($I);
    }
}
