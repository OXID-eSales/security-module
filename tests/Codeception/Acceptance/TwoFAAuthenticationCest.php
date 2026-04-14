<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Account\UserLogin;
use OxidEsales\Codeception\Page\Account\UserPasswordReminder;
use OxidEsales\Codeception\Page\DataObject\ContactData;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_two_fa
 */
class TwoFAAuthenticationCest extends BaseCest
{
    private string $otpInput = '#auth_code';
    private string $twofaCheckbox = '#twofa_enabled';

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $this->setPasswordState(false);
        $this->setTwoFactorAuthState(true);
        $this->setUserTwoFAState($I, false);
    }

    public function testEnablingTwoFAViaSettingsTriggersOtpOnNextLogin(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);

        $I->amOnPage($userLoginPage->URL);
        $userAccountPage = $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        $I->amOnPage('?cl=account_security');
        $I->waitForPageLoad();
        $I->checkOption($this->twofaCheckbox);
        $I->click(Translator::translate('SAVE'));
        $I->waitForPageLoad();

        $userAccountPage->logoutUserInAccountPage();
        $I->waitForPageLoad();

        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        $I->seeElement($this->otpInput);
    }

    public function testLoginOtpBehaviourChangesWithUserTwoFASetting(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);

        $this->setUserTwoFAState($I, true);

        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();
        $I->seeElement($this->otpInput);

        $this->setUserTwoFAState($I, false);

        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();
        $I->dontSeeElement($this->otpInput);
    }

    public function testSettingsPageReflectsTwoFAState(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);

        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        $I->amOnPage('?cl=account_security');
        $I->waitForPageLoad();
        $I->dontSeeCheckboxIsChecked($this->twofaCheckbox);

        $I->checkOption($this->twofaCheckbox);
        $I->click(Translator::translate('SAVE'));
        $I->waitForPageLoad();

        $I->amOnPage('?cl=account_security');
        $I->waitForPageLoad();
        $I->seeCheckboxIsChecked($this->twofaCheckbox);

        $I->uncheckOption($this->twofaCheckbox);
        $I->click(Translator::translate('SAVE'));
        $I->waitForPageLoad();

        $I->amOnPage('?cl=account_security');
        $I->waitForPageLoad();
        $I->dontSeeCheckboxIsChecked($this->twofaCheckbox);
    }

    public function testUnauthenticatedAccessToAccountSecurityRedirectsToLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('?cl=account_security');
        $I->waitForPageLoad();

        $I->see(Translator::translate('LOGIN'));
        $I->dontSeeElement($this->twofaCheckbox);
    }

    public function testShopLevelTwoFADisabledOverridesUserSetting(AcceptanceTester $I): void
    {
        $this->setUserTwoFAState($I, true);
        $this->setTwoFactorAuthState(false);

        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        $I->dontSeeElement($this->otpInput);
    }

    public function testInvalidCodeShowsError(AcceptanceTester $I): void
    {
        $this->setUserTwoFAState($I, true);

        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        $I->fillField($this->otpInput, '000000');
        $I->click('#auth_submit');
        $I->waitForPageLoad();

        $I->see(Translator::translate('ERROR_INVALID_CODE'));
    }

    public function testWrongCodeDecreasesRemainingAttemptsAndShowsError(AcceptanceTester $I): void
    {
        $this->setUserTwoFAState($I, true);

        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        $attemptsBefore = (int) $I->grabTextFrom('#remaining-attempts');

        $I->fillField($this->otpInput, '000000');
        $I->click('#auth_submit');
        $I->waitForPageLoad();

        $I->see(Translator::translate('ERROR_INVALID_CODE'));
        $I->see((string) ($attemptsBefore - 1), '#remaining-attempts');
    }

    public function testResendButtonIsDisabledOnOtpPageLoad(AcceptanceTester $I): void
    {
        $this->setUserTwoFAState($I, true);

        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        // A code was just sent at login, so the server-driven cooldown should disable the button
        $I->waitForJS("return document.getElementById('resend-btn').disabled === true", 5);
        $I->seeElement('#resend-btn[disabled]');
    }

    public function testResendButtonShowsCountdownAfterClick(AcceptanceTester $I): void
    {
        $this->setUserTwoFAState($I, true);

        $userData = $this->getExistingUserData();
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);
        $I->waitForPageLoad();

        // Backdate LAST_SENT_AT so the server reports zero remaining cooldown
        $I->updateInDatabase(
            'oesm_2fa_otp',
            ['LAST_SENT_AT' => date('Y-m-d H:i:s', strtotime('-2 minutes'))],
            ['OXUSERID' => $userData['userId']]
        );

        // Clear stored cooldown from localStorage so JS reads from the (now zero) server value
        $I->executeJS("localStorage.clear()");
        $I->reloadPage();
        $I->waitForPageLoad();

        $I->seeElement('#resend-btn:not([disabled])');

        $I->click('#resend-btn');

        $I->waitForJS(
            "return document.getElementById('resend-btn').textContent.includes('Resend in')",
            30
        );
    }
}
