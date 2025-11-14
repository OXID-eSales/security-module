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
        '//div[contains(@class, "sign-in-providers")]' .
        '//div[@class="provider"]' .
        '//a[contains(@href, "cl=oauth&fnc=redirect&provider=")]';

    public function testProvidersVisibilityOnHeaderLogin(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $homePage->openAccountMenu();
        $I->dontSeeElement($this->providerLink);

        $this->setProviderState(true);
        $homePage = $I->openShop();
        $homePage->openAccountMenu();
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($this->providerLink)));
    }

    public function testProvidersVisibilityOnMyAccountLogin(AcceptanceTester $I): void
    {
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->dontSeeElement($this->providerLink);

        $this->setProviderState(true);
        $userLoginPage = new UserLogin($I);
        $I->amOnPage($userLoginPage->URL);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($this->providerLink)));
    }

    public function testProviderVisibilityOnCheckoutPage(AcceptanceTester $I): void
    {
        $basket = new Basket($I);
        $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $I->waitForElementNotVisible($this->providerLink);

        $this->setProviderState(true);
        $basket = new Basket($I);
        $basket->addProductToBasketAndOpenUserCheckout('1000', 1);
        $I->waitForElementNotVisible($this->providerLink);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($this->providerLink)));
    }

    public function testProviderVisibilityOnCheckoutWithoutAccount(AcceptanceTester $I): void
    {
        $basket = new Basket($I);
        $basket
            ->addProductToBasketAndOpenUserCheckout('1000', 1)
            ->selectOptionNoRegistration();
        $I->waitForElementNotVisible($this->providerLink);

        $this->setProviderState(true);
        $basket
            ->addProductToBasketAndOpenUserCheckout('1000', 1)
            ->selectOptionNoRegistration();
        $I->waitForElementNotVisible($this->providerLink);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($this->providerLink)));
    }

    public function testProviderVisibilityOnCheckoutWithNewAccount(AcceptanceTester $I): void
    {
        $basket = new Basket($I);
        $basket
            ->addProductToBasketAndOpenUserCheckout('1000', 1)
            ->selectOptionRegisterNewAccount();
        $I->waitForElementNotVisible($this->providerLink);

        $this->setProviderState(true);
        $basket
            ->addProductToBasketAndOpenUserCheckout('1000', 1)
            ->selectOptionRegisterNewAccount();
        $I->waitForElementNotVisible($this->providerLink);
        $I->assertGreaterOrEquals(1, count($I->grabMultiple($this->providerLink)));
    }
}
