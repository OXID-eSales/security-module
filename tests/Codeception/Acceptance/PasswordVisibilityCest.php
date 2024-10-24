<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_password_visibility
 */
class PasswordVisibilityCest
{
    private string $eyeIcon = "//div[contains(@class, 'password-toggle')]";

    public function _before(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $I->updateInDatabase(
            'oxuser',
            [
                'oxupdatekey' => 'test_update_key',
                'oxupdateexp' => time() + 60,
            ],
            [
                'oxusername' => $userData['userLoginName'],
            ]
        );
    }

    public function testToggleVisibilityOfRegistrationPassword(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $homePage->openUserRegistrationPage();

        $I->seeElement('//input[@id="userPassword"][@type="password"]');

        // Click on the eye icon to reveal the password
        $I->click($this->eyeIcon);

        $I->seeElement('//input[@id="userPassword"][@type="text"]');
    }

    public function testGenerateUserChangePassword(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $userName = $userData['userLoginName'];
        $userPassword = $userData['userPassword'];

        $homePage = $I->openShop();
        $homePage->loginUser($userName, $userPassword);

        $homePage
            ->openAccountPage()
            ->seePageOpened()
            ->seeUserAccount($userData)
            ->openChangePasswordPage();

        $I->seeElement('//input[@id="passwordNew"][@type="password"]');

        // Click on the eye icon to reveal the password
        $I->click($this->eyeIcon);

        $I->seeElement('//input[@id="passwordNew"][@type="text"]');
    }

    public function testGenerateUserForgotPassword(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $uid = md5($userData['userId'] . '1' . 'test_update_key');
        $I->amOnPage('?cl=forgotpwd&uid=' . $uid . '&lang=1&shp=1');

        $I->seeElement('//input[@id="password_new"][@type="password"]');

        // Click on the eye icon to reveal the password
        $I->click($this->eyeIcon);

        $I->seeElement('//input[@id="password_new"][@type="text"]');
    }

    private function getExistingUserData()
    {
        return Fixtures::get('existingUser');
    }
}
