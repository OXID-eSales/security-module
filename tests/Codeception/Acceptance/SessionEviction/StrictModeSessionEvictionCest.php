<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance\SessionEviction;

use OxidEsales\Codeception\Step\Basket;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_session_eviction
 */
final class StrictModeSessionEvictionCest extends SessionEvictionBaseCest
{
    private const PRODUCT_ID = '1000';

    public function strictModeEndsUnverifiableSessionCompletely(AcceptanceTester $I): void
    {
        $I->wantToTest('strict mode ends a session without login token: user, basket, remember-me cookie, session id');
        $this->setSessionEvictionState(false);
        $this->loginWithRememberMeCookieOnly($I);
        $this->seeLoggedIn($I);
        (new Basket($I))->addProductToBasket(self::PRODUCT_ID, 1);
        $I->openShop()->seeItemCountBadge('1');
        $sessionId = $I->grabCookie(self::SESSION_COOKIE);

        $this->setSessionEvictionState(true, true);

        $this->seeLoggedOut($I);
        $I->dontSeeCookie(self::REMEMBER_ME_COOKIE);
        $I->assertNotEquals($sessionId, $I->grabCookie(self::SESSION_COOKIE));
        $I->openShop()->dontSeeItemCountBadge();
        $this->seeLoggedOut($I);
    }

    public function strictModeKeepsSessionWithLoginToken(AcceptanceTester $I): void
    {
        $I->wantToTest('strict mode keeps a session that was opened by a normal login');
        $this->setSessionEvictionState(true, true);

        $this->login($I);

        $this->seeLoggedIn($I);
        $this->seeLoggedIn($I);
    }

    public function nonStrictModeKeepsUnverifiableSession(AcceptanceTester $I): void
    {
        $I->wantToTest('without strict mode a session without login token stays logged in');
        $this->setSessionEvictionState(false);
        $this->loginWithRememberMeCookieOnly($I);
        $this->seeLoggedIn($I);

        $this->setSessionEvictionState(true);

        $this->seeLoggedIn($I);
    }
}
