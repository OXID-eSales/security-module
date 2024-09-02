<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Codeception\Acceptance;

use Codeception\Example;
use Codeception\Util\Fixtures;
use OxidEsales\SecurityModule\Tests\Codeception\Support\AcceptanceTester;

/**
 * @group oe_security_module
 * @group oe_security_module_password_strength
 */
class PasswordStrengthCest
{
    private $progressBarStrength1 = "//div[contains(@class, 'progress-bar')][contains(@style,'width: 15%')]";
    private $progressBarStrength = "//div[contains(@class, 'progress-bar')]";

    /**
     * @dataProvider passwordDataProvider
     */
    public function testUserRegistrationPassword(AcceptanceTester $I, Example $example): void
    {
        $homePage = $I->openShop();
        $homePage->openUserRegistrationPage();

        $I->pressKey('#userPassword', $example['password']);

        $I->waitForText($example['text'], 10, $this->progressBarStrength);
        $I->seeElement($example['class']);
    }

    /**
     * @dataProvider passwordDataProvider
     */
    public function testUserChangePassword(AcceptanceTester $I, Example $example): void
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

        $I->pressKey('#passwordNew', $example['password']);

        $I->waitForText($example['text'], 10, $this->progressBarStrength);
        $I->seeElement($example['class']);
    }

//    public function testUserForgotPassword(AcceptanceTester $I): void
//    {
//        $homePage = $I->openShop();
//    }

    protected function passwordDataProvider(): array
    {
        return [
            [
                'password' => ['a', 'b'],
                'text' => 'Very weak',
                'class' => '.very-weak'
            ],
            [
                'password' => ['a', 'b', 'c', 'd', 'e', 'f', '1', '2', '3', '!'],
                'text' => 'Weak',
                'class' => '.weak'
            ],
            [
                'password' => ['a', 'b', 'c', 'd', 'e', 'f', '1', '2', '3', '!', 'G', 'H', '/'],
                'text' => 'Medium',
                'class' => '.medium'
            ],
            [
                'password' => ['a', 'b', 'c', 'd', 'e', 'f', '1', '2', '3', '!', 'G', 'H', '/', '4', 'i', '5'],
                'text' => 'Strong',
                'class' => '.strong'
            ],
            [
                'password' => ['a', 'b', 'c', 'd', 'e', 'f', '1', '2', '3', '!', 'G', 'H', '/', '4', 'i', '5', ';', 'j', 'K'],
                'text' => 'Very strong',
                'class' => '.very-strong'
            ],
        ];
    }

    private function getExistingUserData()
    {
        return Fixtures::get('existingUser');
    }
}
