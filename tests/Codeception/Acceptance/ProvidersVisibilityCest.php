<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Page\Account\UserLogin;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_providers_visibility
 */
class ProvidersVisibilityCest extends BaseCest
{
    public function _before(AcceptanceTester $I): void
    {
        $this->setProviderState(false);
    }

    //todo: add % to link to check all providers individually
    private $providerLink =
        'div[contains(@class, "sign-in-providers")]' .
        '//div[@class="provider"]' .
        '//a[contains(@href, "cl=oauth&fnc=redirect&provider=")]';

    public function testProvidersVisibilityOnHeaderLogin(AcceptanceTester $I): void
    {
        $providerLinkElement = '//div[contains(@class, "dropdown-menu")]//' . $this->providerLink;

        $homePage = $I->openShop();
        $homePage->openAccountMenu();
        $I->dontSeeElement($providerLinkElement);

        $this->setProviderState(true);
        $homePage = $I->openShop();
        $homePage->openAccountMenu();
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($providerLinkElement)));
    }

    public function testProvidersVisibilityOnMyAccountLogin(AcceptanceTester $I): void
    {
        $providerLinkElement = '//div[contains(@class, "card-body")]//' . $this->providerLink;

        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->dontSeeElement($providerLinkElement);

        $this->setProviderState(true);
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($providerLinkElement)));
    }

    public function testProviderVisibilityOnCheckoutPage(AcceptanceTester $I): void
    {
        $providerLinkElement = '//div[contains(@class, "card-body")]//' . $this->providerLink;

        $basket = new Basket($I);
        $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $I->dontSeeElement($providerLinkElement);

        $this->setProviderState(true);
        $basket = new Basket($I);
        $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $I->seeElement($providerLinkElement);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($providerLinkElement)));
    }

    public function testProviderVisibilityOnCheckoutWithoutAccount(AcceptanceTester $I): void
    {
        $providerLinkElement = '//div[contains(@class, "card-body")]//' . $this->providerLink;

        $basket = new Basket($I);
        $userCheckout = $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $userCheckout->selectOptionNoRegistration();
        $I->dontSeeElement($providerLinkElement);

        $this->setProviderState(true);
        $basket = new Basket($I);
        $userCheckout = $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $userCheckout->selectOptionNoRegistration();
        $I->seeElement($providerLinkElement);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($providerLinkElement)));
    }

    public function testProviderVisibilityOnCheckoutWithNewAccount(AcceptanceTester $I): void
    {
        $providerLinkElement = '//div[contains(@class, "card-body")]//' . $this->providerLink;

        $basket = new Basket($I);
        $basket
            ->addProductToBasketAndOpenUserCheckout('1000', 1)
            ->selectOptionRegisterNewAccount();
        $I->dontSeeElement($providerLinkElement);

        $this->setProviderState(true);
        $basket
            ->addProductToBasketAndOpenUserCheckout('1000', 1)
            ->selectOptionRegisterNewAccount();
        $I->seeElement($providerLinkElement);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($providerLinkElement)));
    }
}
