<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Example;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface as PasswordSettingsServiceInterface;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_password_disabled
 */
class PasswordDesabledCest extends BaseCest
{
    private string $registerPwdFieldId = "#userPassword";
    private string $changePwdFieldId = "#passwordNew";
    private string $forgotPwdFieldId = "#password_new";
    private string $pwdRequirementsForm = "password-requirements";

    public function _before(AcceptanceTester $I): void
    {
        ContainerFacade::get(CaptchaSettingsServiceInterface::class)->saveIsCaptchaEnabled(false);
        ContainerFacade::get(PasswordSettingsServiceInterface::class)->saveIsPasswordPolicyEnabled(false);
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

    /**
     * @dataProvider passwordDataProvider
     */
    public function testPasswordPolicyDisabledOnRegistrationPage(AcceptanceTester $I, Example $example): void
    {
        ContainerFacade::get(PasswordSettingsServiceInterface::class)->saveIsPasswordPolicyEnabled($example['enabled']);
        $homePage = $I->openShop();
        $homePage->openUserRegistrationPage();

        $I->click($this->registerPwdFieldId);
        $I->dontSeeElement($this->pwdRequirementsForm);
    }

    /**
     * @dataProvider passwordDataProvider
     */
    public function testPasswordPolicyDisabledOnChangePassword(AcceptanceTester $I, Example $example): void
    {
        ContainerFacade::get(PasswordSettingsServiceInterface::class)->saveIsPasswordPolicyEnabled($example['enabled']);
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

        $I->click($this->changePwdFieldId);
        $I->dontSeeElement($this->pwdRequirementsForm);
    }

    /**
     * @dataProvider passwordDataProvider
     */
    public function testPasswordPolicyDisabledOnForgotPassword(AcceptanceTester $I, Example $example): void
    {
        ContainerFacade::get(PasswordSettingsServiceInterface::class)->saveIsPasswordPolicyEnabled($example['enabled']);
        $userData = $this->getExistingUserData();
        $uid = md5($userData['userId'] . '1' . 'test_update_key');
        $I->amOnPage('?cl=forgotpwd&uid=' . $uid . '&lang=1&shp=1');

        $I->click($this->forgotPwdFieldId);
        $I->dontSeeElement($this->pwdRequirementsForm);
    }

    private function checkVisibility(AcceptanceTester $I, bool $visible)
    {
        if ($visible) {
            $I->seeElement($this->pwdRequirementsForm);
        } else {
            $I->dontSeeElement($this->pwdRequirementsForm);
        }
    }

    protected function passwordDataProvider(): array
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
