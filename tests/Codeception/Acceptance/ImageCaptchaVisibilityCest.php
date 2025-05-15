<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Example;
use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Account\UserLogin;
use OxidEsales\Codeception\Page\Account\UserPasswordReminder;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_captcha_visibility
 */
class ImageCaptchaVisibilityCest extends BaseCest
{
    private string $captchaImage = "//div[contains(@class, 'image-captcha')]//div[2]//img";
    private string $captchaInput = "//div[contains(@class, 'image-captcha')]//div[1]//div//input";

    /**
     * @dataProvider captchaDataProvider
     */
    public function testCaptchaImageOnRegistrationPage(AcceptanceTester $I, Example $example): void
    {
        $this->setCaptchaState($example['enabled']);

        $homePage = $I->openShop();
        $homePage->openUserRegistrationPage();

        $this->checkVisibility($I, $example['visibility']);
    }

    /**
     * @dataProvider captchaDataProvider
     */
    public function testCaptchaImageOnLoginPage(AcceptanceTester $I, Example $example): void
    {
        $this->setCaptchaState($example['enabled']);

        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->see(Translator::translate('LOGIN'));

        $this->checkVisibility($I, $example['visibility']);
    }

    /**
     * @dataProvider captchaDataProvider
     */
    public function testCaptchaImageOnContactPage(AcceptanceTester $I, Example $example): void
    {
        $this->setCaptchaState($example['enabled']);

        $homePage = $I->openShop();
        $homePage->openContactPage();

        $this->checkVisibility($I, $example['visibility']);
    }

    /**
     * @dataProvider captchaDataProvider
     */
    public function testCaptchaImageOnNewsletterPage(AcceptanceTester $I, Example $example): void
    {
        $this->setCaptchaState($example['enabled']);

        $userData = Fixtures::get('existingUser');

        $homePage = $I->openShop();
        $homePage->subscribeForNewsletter($userData['userLoginName']);

        $this->checkVisibility($I, $example['visibility']);
    }

    /**
     * @dataProvider captchaDataProvider
     */
    public function testCaptchaImageOnLoginBox(AcceptanceTester $I, Example $example): void
    {
        $this->setCaptchaState($example['enabled']);

        $homePage = $I->openShop();
        $homePage->openAccountMenu();

        $this->checkVisibility($I, $example['visibility']);
    }

    /**
     * @dataProvider captchaDataProvider
     */
    public function testCaptchaImageOnForgotPasswordPage(AcceptanceTester $I, Example $example): void
    {
        $this->setCaptchaState($example['enabled']);

        $forgotPwdPage = new UserPasswordReminder($I);
        $I->amOnPage($forgotPwdPage->URL);

        $this->checkVisibility($I, $example['visibility']);
    }

    /**
     * @dataProvider captchaDataProvider
     */
    public function testCaptchaImageOnCheckoutWithoutRegistration(AcceptanceTester $I, Example $example): void
    {
        $this->setCaptchaState($example['enabled']);

        $basket = new Basket($I);
        $userCheckout = $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $userCheckout->selectOptionNoRegistration();

        $this->checkVisibility($I, $example['visibility']);
    }

    private function checkVisibility(AcceptanceTester $I, bool $visible)
    {
        if ($visible) {
            $I->seeElement($this->captchaImage);
            $image = $I->grabAttributeFrom($this->captchaImage, 'src');
            $I->assertFalse(empty($image));
            $I->seeElement($this->captchaInput);
        } else {
            $I->dontSeeElement($this->captchaImage);
            $I->dontSeeElement($this->captchaInput);
        }
    }

    protected function captchaDataProvider(): array
    {
        return [
            [
                'enabled' => false,
                'visibility' => false
            ],
            [
                'enabled' => true,
                'visibility' => true
            ],
        ];
    }
}
