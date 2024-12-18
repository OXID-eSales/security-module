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
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_captcha_visibility
 */
class ImageCaptchaVisibilityCest
{
    private string $captchaImage = "//div[contains(@class, 'image-captcha')]//div[2]//img";
    private string $captchaInput = "//div[contains(@class, 'image-captcha')]//div[1]//div//input";

    public function testCaptchaImageOnRegistrationPage(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $homePage->openUserRegistrationPage();

        $this->checkVisibility($I);
    }

    public function testCaptchaImageOnLoginPage(AcceptanceTester $I): void
    {
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->see(Translator::translate('LOGIN'));

        $this->checkVisibility($I);
    }

    public function testCaptchaImageOnContactPage(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $homePage->openContactPage();

        $this->checkVisibility($I);
    }

    public function testCaptchaImageOnNewsletterPage(AcceptanceTester $I): void
    {
        $userData = Fixtures::get('existingUser');

        $homePage = $I->openShop();
        $homePage->subscribeForNewsletter($userData['userLoginName']);

        $this->checkVisibility($I);
    }

    public function testCaptchaImageOnLoginBox(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $homePage->openAccountMenu();

        $this->checkVisibility($I);
    }

    private function checkVisibility(AcceptanceTester $I)
    {
        $I->seeElement($this->captchaImage);
        $image = $I->grabAttributeFrom($this->captchaImage, 'src');
        $I->assertFalse(empty($image));

        $I->seeElement($this->captchaInput);
    }
}
