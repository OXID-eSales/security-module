<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance\SessionEviction;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Home;
use OxidEsales\SecurityModule\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * Feature on: a storefront session re-opened from the persistent login cookie
 * carries a login-token bound to the current password, so it is ended on its next
 * request after the password changes (FR001/FR002/FR003).
 *
 * @group oe_security_module
 * @group oe_security_module_session_eviction
 * @group flow:remember-me-session-eviction
 */
class RememberMeSessionEvictionCest extends BaseCest
{
    private string $accountUrl = '/en/my-account/';
    private string $loginEmailFieldId = '#loginEmail';
    private string $loginPasswordFieldId = '#loginPasword';
    private string $rememberMeCheckboxId = '#remember';
    private string $loginButton = '//form[@name="login"]/button';
    private string $rememberMeCookie = 'oxid_1';
    private string $sessionCookie = 'sid';
    private string $sessionKeyCookie = 'sid_key';
    private string $accountLoginButtonId = '#loginButton';

    private bool $originalRememberMe = false;

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $this->setHoneyPotCaptchaState(false);
        $this->setPasswordState(false);
        $this->setSessionEvictionState(true);
        $this->restoreFixtureUserPassword($I);

        $config = Registry::getConfig();
        $this->originalRememberMe = (bool) $config->getConfigParam('blShowRememberMe');
        $config->saveShopConfVar('bool', 'blShowRememberMe', true);
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->setSessionEvictionState(false);
        Registry::getConfig()->saveShopConfVar('bool', 'blShowRememberMe', $this->originalRememberMe);
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

    public function testRememberMeSessionEndedAfterPasswordChange(AcceptanceTester $I): void
    {
        $I->wantToTest('a remember-me auto-login session is logged out after the password changes');

        $userData = $this->getExistingUserData();

        $homePage = $I->openShop();
        $this->loginWithRememberMe($I, $homePage, $userData['userLoginName'], $userData['userPassword']);
        $I->retrySeeCookie($this->rememberMeCookie);

        // Keep the persistent login cookie but clear the session cookies.
        $I->resetCookie($this->sessionCookie);
        $I->resetCookie($this->sessionKeyCookie);

        // The next request re-opens the session from the persistent login cookie.
        $I->amOnPage($this->accountUrl);
        $this->seeLoggedIn($I, $userData);

        // The password is changed elsewhere (updated directly in the database).
        $I->updateInDatabase(
            'oxuser',
            ['OXPASSWORD' => password_hash('ChangedElsewhere987!', PASSWORD_BCRYPT)],
            ['OXID' => $userData['userId']]
        );

        // On the next request the bound login-token no longer matches, so the session is ended.
        $I->amOnPage($this->accountUrl);
        $this->seeLoggedOut($I, $userData);
    }

    public function testRememberMeKeepsSessionWhilePasswordUnchanged(AcceptanceTester $I): void
    {
        $I->wantToTest('a remember-me auto-login session stays logged in while the password is unchanged');

        $userData = $this->getExistingUserData();

        $homePage = $I->openShop();
        $this->loginWithRememberMe($I, $homePage, $userData['userLoginName'], $userData['userPassword']);
        $I->retrySeeCookie($this->rememberMeCookie);

        $I->resetCookie($this->sessionCookie);
        $I->resetCookie($this->sessionKeyCookie);

        $I->amOnPage($this->accountUrl);
        $this->seeLoggedIn($I, $userData);

        // No password change: a further request must keep the customer logged in.
        $I->amOnPage($this->accountUrl);
        $this->seeLoggedIn($I, $userData);
    }

    private function loginWithRememberMe(
        AcceptanceTester $I,
        Home $homePage,
        string $userName,
        string $password
    ): void {
        $homePage->openAccountMenu();
        $I->waitForElementVisible($this->loginEmailFieldId);
        $I->retryFillField($this->loginEmailFieldId, $userName);
        $I->retryFillField($this->loginPasswordFieldId, $password);
        $I->waitForElementVisible($this->rememberMeCheckboxId);
        $I->checkOption($this->rememberMeCheckboxId);
        $I->retryClick($this->loginButton);
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
