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
 * @group oe_security_module_honeypot_captcha
 */
class HoneyPotCaptchaCest extends BaseCest
{
    private string $honeyPotInput = 'lastname_confirm';
    private string $loginForm = 'main.content form[name="login"]';
    private string $loginBoxForm = 'header form[name="login"]';
    private string $newsletterForm = 'main.content form.needs-validation';
    private string $contactForm = 'main.content form.needs-validation';
    private string $registerForm = 'main.content form[name="order"]';
    private string $forgotPwdForm = 'main.content form[name="forgotpwd"]';

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $this->setPasswordState(false);
    }

    public function testHoneyPotCaptchaOnRegistrationPage(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $registrationPage = $homePage->openUserRegistrationPage();
        $userData = $this->getNewUserData();
        $this->fillRegistrationForm($I, $userData);
        $I->executeJS(
            "document.querySelector('$this->registerForm input[name=\"$this->honeyPotInput\"]').value = 'some value';"
        );
        $I->retryClick($registrationPage->saveFormButton);

        $I->see(Translator::translate('FORM_VALIDATION_FAILED'));
    }

    public function testHoneyPotCaptchaOnContactPage(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $contactPage = $homePage->openContactPage();
        $userData = $this->getExistingUserData();

        $contactData = new ContactData();
        $contactData->setEmail($userData['userLoginName']);
        $contactPage->fillInContactData($contactData);
        $I->executeJS(
            "document.querySelector('$this->contactForm input[name=\"$this->honeyPotInput\"]').value = 'some value';"
        );
        $contactPage->sendContactData();

        $I->see(Translator::translate('FORM_VALIDATION_FAILED'));
    }

    public function testHoneyPotCaptchaOnNewsletterPage(AcceptanceTester $I): void
    {

        $userData = Fixtures::get('existingUser');

        $homePage = $I->openShop();
        $newsletterPage = $homePage->subscribeForNewsletter($userData['userLoginName']);
        $I->executeJS(
            "document.querySelector('$this->newsletterForm input[name=\"$this->honeyPotInput\"]').value = 'some value';"
        );
        $newsletterPage->subscribe();

        $I->see(Translator::translate('FORM_VALIDATION_FAILED'));
    }

    public function testHoneyPotCaptchaOnLoginPage(AcceptanceTester $I): void
    {
        $userData = Fixtures::get('existingUser');

        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->see(Translator::translate('LOGIN'));

        $I->executeJS(
            "document.querySelector('$this->loginForm input[name=\"$this->honeyPotInput\"]').value = 'some value';"
        );
        $userLoginPage->loginWithError($userData['userLoginName'], $userData['userPassword']);

        $I->see(Translator::translate('FORM_VALIDATION_FAILED'));
    }

    public function testHoneyPotCaptchaOnLoginBox(AcceptanceTester $I): void
    {

        $userData = Fixtures::get('existingUser');

        $homePage = $I->openShop();
        $accountMenu = $homePage->openAccountMenu();
        $I->waitForText(Translator::translate('FORGOT_PASSWORD'));
        $I->retryFillField($accountMenu->userLoginName, $userData['userLoginName']);
        $I->retryFillField($accountMenu->userLoginPassword, $userData['userPassword']);
        $I->executeJS(
            "document.querySelector('$this->loginBoxForm input[name=\"$this->honeyPotInput\"]').value = 'some value';"
        );
        $I->retryClick($accountMenu->userLoginButton);
        $I->waitForPageLoad();

        $I->see(Translator::translate('FORM_VALIDATION_FAILED'));
    }

    public function testHoneyPotCaptchaOnForgotPasswordPage(AcceptanceTester $I): void
    {

        $userData = Fixtures::get('existingUser');

        $forgotPwdPage = new UserPasswordReminder($I);
        $I->amOnPage($forgotPwdPage->URL);

        $I->executeJS(
            "document.querySelector('$this->forgotPwdForm input[name=\"$this->honeyPotInput\"]').value = 'some value';"
        );
        $forgotPwdPage->resetPassword($userData['userLoginName']);

        $I->see(Translator::translate('FORM_VALIDATION_FAILED'));
    }

    private function fillRegistrationForm(AcceptanceTester $I, array $userData)
    {
        foreach ($userData['inputFields'] as $key => $value) {
            $I->fillField("#$key", $value);
        }

        foreach ($userData['selectFields'] as $key => $value) {
            $I->selectOption("select#$key", $value);
        }
    }
}
