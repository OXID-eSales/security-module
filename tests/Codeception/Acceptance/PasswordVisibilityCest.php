<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_password_visibility
 */
class PasswordVisibilityCest extends BaseCest
{
    private string $registerPwdFieldId = "userPassword";
    private string $registerConfirmFieldId = "userPasswordConfirm";
    private string $changePwdFieldId = "passwordNew";
    private string $changeOldFieldId = "passwordOld";
    private string $changeConfirmFieldId = "passwordNewConfirm";
    private string $forgotPwdFieldId = "password_new";
    private string $forgotConfirmFieldId = "password_new_confirm";

    public function _before(AcceptanceTester $I): void
    {
        $this->setCaptchaState(false);

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

        $this->toggleAndCheckVisibility($I, $this->registerPwdFieldId);
        $this->toggleAndCheckVisibility($I, $this->registerConfirmFieldId);
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

        $this->toggleAndCheckVisibility($I, $this->changeOldFieldId);
        $this->toggleAndCheckVisibility($I, $this->changePwdFieldId);
        $this->toggleAndCheckVisibility($I, $this->changeConfirmFieldId);
    }

    public function testGenerateUserForgotPassword(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $uid = md5($userData['userId'] . '1' . 'test_update_key');
        $I->amOnPage('?cl=forgotpwd&uid=' . $uid . '&lang=1&shp=1');

        $this->toggleAndCheckVisibility($I, $this->forgotPwdFieldId);
        $this->toggleAndCheckVisibility($I, $this->forgotConfirmFieldId);
    }

    private function toggleAndCheckVisibility(AcceptanceTester $I, string $inputElementId)
    {
        $I->seeElement("//input[@id='$inputElementId'][@type='password']");

        // Click on the eye icon to reveal the password
        $I->click("//div[contains(@class, 'password-toggle')and @data-target='$inputElementId']");

        $I->seeElement("//input[@id='$inputElementId'][@type='text']");
    }
}
