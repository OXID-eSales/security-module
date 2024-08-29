<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_password_strength
 */
class PasswordStrengthCest
{
    private $progressBarStrength = "//div[contains(@class, 'progress-bar')][contains(@style,'width: 50%')]";

    public function testUserRegistrationPassword(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $homePage->openUserRegistrationPage();

        $I->pressKey('#userPassword', ['a', 'b']);
        $I->wait(1); //Wait for animation
        $I->seeElement($this->progressBarStrength);
    }

    public function testUserChangePassword(AcceptanceTester $I): void
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

        $I->pressKey('#passwordNew', ['a', 'b']);
        $I->wait(1); //Wait for animation
        $I->seeElement($this->progressBarStrength);
    }

//    public function testUserForgotPassword(AcceptanceTester $I): void
//    {
//        $homePage = $I->openShop();
//    }

    private function getExistingUserData()
    {
        return Fixtures::get('existingUser');
    }
}
