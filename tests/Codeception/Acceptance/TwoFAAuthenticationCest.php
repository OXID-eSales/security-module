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
    private string $otpSubmitBtn = '#auth_submit';
    private string $otpResendBtn = 'RESEND_CODE';

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $this->setPasswordState(false);
        $this->setTwoFactorAuthState(true);
    }

    public function testRedirectToOTPAfterLogin(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();

        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->see(Translator::translate('LOGIN'));

        $userLoginPage->login($userData['userLoginName'], $userData['userPassword']);

        $I->waitForPageLoad();
        $I->seeElement($this->otpInput);
        $I->seeElement($this->otpSubmitBtn);
        $I->see(Translator::translate($this->otpResendBtn));
    }

    public function testRedirectToOTPOnLoginBox(AcceptanceTester $I): void
    {

        $userData = $this->getExistingUserData();

        $homePage = $I->openShop();
        $accountMenu = $homePage->openAccountMenu();
        $I->waitForText(Translator::translate('FORGOT_PASSWORD'));
        $I->retryFillField($accountMenu->userLoginName, $userData['userLoginName']);
        $I->retryFillField($accountMenu->userLoginPassword, $userData['userPassword']);
        $I->retryClick($accountMenu->userLoginButton);

        $I->waitForPageLoad();
        $I->seeElement($this->otpInput);
        $I->seeElement($this->otpSubmitBtn);
        $I->see(Translator::translate($this->otpResendBtn));
    }
}
