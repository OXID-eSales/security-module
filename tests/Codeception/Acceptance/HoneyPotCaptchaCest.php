<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\DataObject\ContactData;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_honeypot_captcha
 */
class HoneyPotCaptchaCest extends BaseCest
{
    private string $honeyPotInput = "lastname_confirm";

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
        $I->executeJS("document.querySelector('input[name=\"$this->honeyPotInput\"]').value = 'some value';");
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
        $I->executeJS("document.querySelector('input[name=\"$this->honeyPotInput\"]').value = 'some value';");
        $contactPage->sendContactData();

        $I->see(Translator::translate('FORM_VALIDATION_FAILED'));
    }

    public function testHoneyPotCaptchaOnNewsletterPage(AcceptanceTester $I): void
    {

        $userData = Fixtures::get('existingUser');

        $homePage = $I->openShop();
        $newsletterPage = $homePage->subscribeForNewsletter($userData['userLoginName']);
        $I->executeJS("document.querySelector('input[name=\"$this->honeyPotInput\"]').value = 'some value';");
        $newsletterPage->subscribe();

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
