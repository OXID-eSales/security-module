<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Context;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Exception\ModuleSetupException;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

abstract class BaseCest
{
    private string $captchaInput = "//div[contains(@class, 'image-captcha')]//div[1]//div//input";
    private string $captchaDebugValue = "TEST123";

    protected function loginWithCaptcha(AcceptanceTester $I, string $userName, string $userPassword)
    {
        $homePage = $I->openShop();
        $homePage->openAccountMenu();
        $I->waitForText(Translator::translate('FORGOT_PASSWORD'));
        $I->waitForElementVisible($homePage->userLoginName);
        $I->retryFillField($homePage->userLoginName, $userName);
        $I->retryFillField($homePage->userLoginPassword, $userPassword);
        $I->retryFillField($this->captchaInput, $this->captchaDebugValue);
        $I->retryClick($homePage->userLoginButton);
        $I->waitForPageLoad();
        Context::setActiveUser($userName);

        return $homePage;
    }

    protected function getExistingUserData()
    {
        return Fixtures::get('existingUser');
    }
}
