<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Example;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_password_errors
 */
class PasswordErrorsCest extends BaseCest
{
    private string $forgotPwdFieldId = "password_new";
    private string $forgotConfirmFieldId = "password_new_confirm";
    private string $forgotPwdButton = ".submitButton";

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

    /**
     * @dataProvider passwordDataProvider
     */
    public function testMultipleErrorsOnRegistrationPassword(AcceptanceTester $I, Example $example): void
    {
        $homePage = $I->openShop();
        $registrationPage = $homePage->openUserRegistrationPage();
        $userData = $this->getNewUserData();
        $userData['loginData']['userPasswordField'] = $example['password'];
        $registrationPage->enterUserLoginData($userData['loginData']);
        $registrationPage->enterAddressData($userData['address']);
        $I->clickWithLeftButton($registrationPage->saveFormButton);

        $this->checkErrorsVisibility($I, $example['errors']);
    }

    /**
     * @dataProvider passwordDataProvider
     */
    public function testMultipleErrorsOnChangePassword(AcceptanceTester $I, Example $example): void
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
            ->openChangePasswordPage()
            ->changePassword($userPassword, $example['password'], $example['password']);

        $this->checkErrorsVisibility($I, $example['errors']);
    }

    /**
     * @dataProvider passwordDataProvider
     */
    public function testMultipleErrorsOnForgotPassword(AcceptanceTester $I, Example $example): void
    {
        $userData = $this->getExistingUserData();
        $uid = md5($userData['userId'] . '1' . 'test_update_key');
        $I->amOnPage('?cl=forgotpwd&uid=' . $uid . '&lang=1&shp=1');

        $I->fillField($this->forgotPwdFieldId, $example['password']);
        $I->fillField($this->forgotConfirmFieldId, $example['password']);
        $I->click($this->forgotPwdButton);

        $this->checkErrorsVisibility($I, $example['errors']);
    }

    protected function passwordDataProvider(): array
    {
        return [
            [
                'password' => 'somepwd',
                'errors' => [
                    'ERROR_PASSWORD_MISSING_DIGIT',
                    'ERROR_PASSWORD_MIN_LENGTH',
                    'ERROR_PASSWORD_MISSING_UPPER_CASE',
                    'ERROR_PASSWORD_MISSING_SPECIAL_CHARACTER',
                ]
            ],
            [
                'password' => 'N0LOWERANDCHARACTER',
                'errors' => [
                    'ERROR_PASSWORD_MISSING_LOWER_CASE',
                    'ERROR_PASSWORD_MISSING_SPECIAL_CHARACTER',
                ]
            ],
            [
                'password' => 'nocapital&number',
                'errors' => [
                    'ERROR_PASSWORD_MISSING_UPPER_CASE',
                    'ERROR_PASSWORD_MISSING_DIGIT',
                ]
            ],
            [
                'password' => 'nocharacterandcapitalandnumber',
                'errors' => [
                    'ERROR_PASSWORD_MISSING_DIGIT',
                    'ERROR_PASSWORD_MISSING_UPPER_CASE',
                    'ERROR_PASSWORD_MISSING_SPECIAL_CHARACTER',
                ]
            ],
            [
                'password' => 'pWd0!',
                'errors' => [
                    'ERROR_PASSWORD_MIN_LENGTH',
                ]
            ],
        ];
    }

    private function getMinimumPasswordLength(): int
    {
        /** @var ModuleSettingsServiceInterface $passwordPolicySettings */
        $passwordPolicySettings = ContainerFacade::get(ModuleSettingsServiceInterface::class);
        return $passwordPolicySettings->getPasswordMinimumLength();
    }

    private function checkErrorsVisibility(AcceptanceTester $I, array $errors): void
    {
        foreach ($errors as $error) {
            $message = Translator::translate($error);

            if ($error === 'ERROR_PASSWORD_MIN_LENGTH') {
                $message = sprintf($message, $this->getMinimumPasswordLength());
            }

            $I->see($message);
        }
    }
}
