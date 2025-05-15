<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Acceptance;

use OxidEsales\Codeception\Step\Basket;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\SecurityModule\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_checkout
 */
class CheckoutCest extends BaseCest
{
    public function loggedInUserCanCheckout(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);
        $userData = $this->getExistingUserData();
        $homePage = $I->openShop();
        $homePage->loginUser($userData['userLoginName'], $userData['userPassword']);

        $this->setCaptchaState(true);
        $basket = new Basket($I);
        $userCheckout = $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $userCheckout->goToNextStep();
    }

    public function anonymousUserCanCheckout(AcceptanceTester $I): void
    {
        $this->ensureCorrectCaptchaSettings($I);

        $basket = new Basket($I);
        $userCheckout = $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $userCheckout->selectOptionNoRegistration();
        $userCheckout->enterUserLoginName('test-user@oxid-esales.com');
        $userCheckout->enterAddressData([
            'userSalutation' => 'Mrs',
            'userFirstName' => 'John',
            'userLastName' => 'Doe',
            'companyName' => 'XYZ Corp.',
            'street' => 'Main St',
            'streetNr' => '10',
            'ZIP' => '90210',
            'city' => 'Los Angeles',
            'additionalInfo' => 'Floor 5, Office 2',
            'fonNr' => '123-456-7890',
            'faxNr' => '098-765-4321',
            'countryId' => 'Germany',
        ]);
        $userCheckout->goToNextStep();
    }

    private function ensureCorrectCaptchaSettings(AcceptanceTester $I): void
    {
        /** @var CaptchaSettingsServiceInterface $captchaSettings */
        $captchaSettings = ContainerFacade::get(CaptchaSettingsServiceInterface::class);
        $captchaSettings->saveIsCaptchaEnabled(false);
        $I->assertTrue($captchaSettings->isHoneyPotCaptchaEnabled());
    }
}
