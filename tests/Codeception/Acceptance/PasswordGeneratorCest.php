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
 * @group oe_security_module_password_generator
 */
class PasswordGeneratorCest
{
    private string $progressBarStrength = "//div[contains(@class, 'progress-bar')]";

    private string $generatePasswordButton = "#generate_password";

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

    public function testGenerateUserRegistrationPassword(AcceptanceTester $I): void
    {
        $homePage = $I->openShop();
        $homePage->openUserRegistrationPage();

        $I->click('#userPassword');
        $I->waitForElement($this->generatePasswordButton);
        $I->click($this->generatePasswordButton);
        $I->assertFalse(empty($I->grabValueFrom('#userPassword')));
        $I->assertFalse(empty($I->grabValueFrom('#userPasswordConfirm')));

        $I->waitForText(Translator::translate('ERROR_PASSWORD_STRENGTH_4'), 10, $this->progressBarStrength);
        $I->seeElement('.very-strong');
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

        $I->click('#passwordNew');
        $I->waitForElement($this->generatePasswordButton);
        $I->click($this->generatePasswordButton);
        $I->assertFalse(empty($I->grabValueFrom('#passwordNew')));
        $I->assertFalse(empty($I->grabValueFrom('#passwordNewConfirm')));

        $I->waitForText(Translator::translate('ERROR_PASSWORD_STRENGTH_4'), 10, $this->progressBarStrength);
        $I->seeElement('.very-strong');
    }

    public function testGenerateUserForgotPassword(AcceptanceTester $I): void
    {
        $userData = $this->getExistingUserData();
        $uid = md5($userData['userId'] . '1' . 'test_update_key');
        $I->amOnPage('?cl=forgotpwd&uid=' . $uid . '&lang=1&shp=1');

        $I->click('#password_new');
        $I->waitForElement($this->generatePasswordButton);
        $I->click($this->generatePasswordButton);
        $I->assertFalse(empty($I->grabValueFrom('#password_new')));
        $I->assertFalse(empty($I->grabValueFrom('#password_new_confirm')));

        $I->waitForText(Translator::translate('ERROR_PASSWORD_STRENGTH_4'), 10, $this->progressBarStrength);
        $I->seeElement('.very-strong');
    }

    private function getExistingUserData()
    {
        return Fixtures::get('existingUser');
    }
}
