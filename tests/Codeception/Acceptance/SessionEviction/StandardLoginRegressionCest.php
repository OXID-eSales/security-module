<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance\SessionEviction;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\SecurityModule\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * Regression guard (FR008): a plain interactive login (no remember-me) binds a core
 * login-token, so a foreign password change must still log the session out on its next
 * request. This is the documented OXID 7.2 behaviour and the feature must preserve it,
 * both with the feature on (it must not weaken the logout) and with the feature off (core
 * already does it).
 *
 * @group oe_security_module
 * @group oe_security_module_session_eviction
 * @group flow:interactive-login-session-eviction
 */
class StandardLoginRegressionCest extends BaseCest
{
    private string $accountUrl = '/en/my-account/';
    private string $loginUserFieldId = '#loginUser';
    private string $loginPasswordFieldId = '#loginPwd';
    private string $accountLoginButtonId = '#loginButton';
    private string $rememberMeCookie = 'oxid_1';
    private string $sessionCookie = 'sid';
    private string $sessionKeyCookie = 'sid_key';

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $this->setHoneyPotCaptchaState(false);
        $this->setPasswordState(false);
        $this->restoreFixtureUserPassword($I);
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->setSessionEvictionState(false);
        $this->restoreFixtureUserPassword($I);
    }

    private function restoreFixtureUserPassword(AcceptanceTester $I): void
    {
        // The Db module keeps changes between tests (cleanup: false). Restore the fixture
        // user's known password ('useruser') before AND after each test, so a password
        // changed in a test never leaks into a later test or a later Cest.
        $userData = $this->getExistingUserData();
        $I->updateInDatabase(
            'oxuser',
            [
                'OXPASSWORD' => 'c9dadd994241c9e5fa6469547009328a',
                'OXPASSSALT' => '7573657275736572',
            ],
            ['OXID' => $userData['userId']]
        );
    }

    public function testStandardLoginIsLoggedOutAfterPasswordChangeWithFeatureOn(
        AcceptanceTester $I
    ): void {
        $this->setSessionEvictionState(true);
        $I->wantToTest('feature on: standard login is still logged out after the password changes');

        $this->assertStandardLoginLogoutAfterPasswordChange($I);
    }

    public function testStandardLoginIsLoggedOutAfterPasswordChangeWithFeatureOff(
        AcceptanceTester $I
    ): void {
        $this->setSessionEvictionState(false);
        $I->wantToTest('feature off: standard login is logged out after the password changes (core)');

        $this->assertStandardLoginLogoutAfterPasswordChange($I);
    }

    private function assertStandardLoginLogoutAfterPasswordChange(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();

        // Start from a guaranteed logged-out state: a remember-me cookie left by an earlier
        // Cest in the suite would otherwise auto-login this browser and hide the login form.
        $I->openShop();
        $I->resetCookie($this->rememberMeCookie);
        $I->resetCookie($this->sessionCookie);
        $I->resetCookie($this->sessionKeyCookie);

        $this->standardLogin($I, $userData['userLoginName'], $userData['userPassword']);

        $I->amOnPage($this->accountUrl);
        $this->seeLoggedIn($I, $userData);

        // The password is changed elsewhere (directly in the database), so this
        // interactive session's bound login-token becomes stale.
        $I->updateInDatabase(
            'oxuser',
            ['OXPASSWORD' => password_hash('ChangedElsewhere987!', PASSWORD_BCRYPT)],
            ['OXID' => $userData['userId']]
        );

        $I->amOnPage($this->accountUrl);
        $this->seeLoggedOut($I, $userData);
    }

    private function standardLogin(AcceptanceTester $I, string $userName, string $password): void
    {
        $I->amOnPage($this->accountUrl);
        $I->waitForElementVisible($this->loginUserFieldId);
        $I->retryFillField($this->loginUserFieldId, $userName);
        $I->retryFillField($this->loginPasswordFieldId, $password);
        $I->retryClick($this->accountLoginButtonId);
        $I->waitForPageLoad();
    }

    private function seeLoggedIn(AcceptanceTester $I, array $userData): void
    {
        $I->see(Translator::translate('LOGOUT'));
        $I->see(Translator::translate('HELLO') . ' ' . $userData['userName']);
        $I->dontSeeElement($this->accountLoginButtonId);
    }

    private function seeLoggedOut(AcceptanceTester $I, array $userData): void
    {
        $I->seeElement($this->accountLoginButtonId);
        $I->see(Translator::translate('LOGIN'), 'h1');
        $I->dontSee(Translator::translate('HELLO') . ' ' . $userData['userName']);
    }
}
