<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;

class UserTest extends IntegrationTestCase
{
    private const OTP_USER_NAME = 'user@oxid-esales.com';
    private const OTP_USER_PASSWORD = 'useruser';

    protected Request $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter'])
            ->getMock();

        Registry::set(Request::class, $this->requestMock);
        Registry::getSession()->setVariable('captcha', 'valid_captcha');
        Registry::getSession()->setVariable('captcha_expiration', time() + 60);
    }

    public function tearDown(): void
    {
        $this->disableTwoFactorAuth();
        parent::tearDown();
    }

    public function testCheckValuesWithInvalidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'invalid_captcha';
                }
                return null; //suppress warnings from shop
            });

        $this->expectException(InputException::class);
        $message = Registry::getLang()->translateString("ERROR_INVALID_CAPTCHA");
        $this->expectExceptionMessage($message);

        $subject = oxNew(User::class);
        $subject->checkValues('', '', '', [], []);
    }

    public function testCheckValuesWithEmptyCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return '';
                }
                return null; //suppress warnings from shop
            });

        $this->expectException(InputException::class);
        $message = Registry::getLang()->translateString("ERROR_EMPTY_CAPTCHA");
        $this->expectExceptionMessage($message);

        $subject = oxNew(User::class);
        $subject->checkValues('', '', '', [], []);
    }

    public function testLoginWithInvalidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('invalid_captcha');

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("ERROR_INVALID_CAPTCHA");

        $subject = oxNew(User::class);
        $subject->login('', '');
    }

    public function testLoginWithEmptyCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn('');

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("ERROR_EMPTY_CAPTCHA");

        $subject = oxNew(User::class);
        $subject->login('', '');
    }

    public function testLoginWithOTPEnabledAndValidCredentialsReturnsFalse(): void
    {
        $this->disableCaptcha();
        $this->enableTwoFactorAuth();

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())->method('redirect');
        Registry::set(Utils::class, $utilsMock);

        $subject = oxNew(User::class);
        $result = $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $this->assertFalse($result);
        $this->assertEquals(
            self::OTP_USER_NAME,
            Registry::getSession()->getVariable(AuthorizeService::USER_SESSION_KEY)
        );
    }

    public function testLoginWithOTPEnabledAndInvalidCredentialsThrowsException(): void
    {
        $this->disableCaptcha();
        $this->enableTwoFactorAuth();

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('ERROR_MESSAGE_USER_NOVALIDLOGIN');

        $subject = oxNew(User::class);
        $subject->login(self::OTP_USER_NAME, uniqid());
    }

    public function testLoginWithOTPEnabledAndNonExistentUserThrowsException(): void
    {
        $this->disableCaptcha();
        $this->enableTwoFactorAuth();

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('ERROR_MESSAGE_USER_NOVALIDLOGIN');

        $subject = oxNew(User::class);
        $subject->login('nonexistent@test.com', 'anypassword');
    }

    public function testLoginWithOTPDisabledCallsParentLogin(): void
    {
        $this->disableCaptcha();
        $this->disableTwoFactorAuth();

        $subject = oxNew(User::class);
        $result = $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $this->assertTrue($result);
        $this->assertNull(
            Registry::getSession()->getVariable(AuthorizeService::USER_SESSION_KEY)
        );
    }

    public function testLoginWithOTPEnabledStoresUserInSession(): void
    {
        $this->disableCaptcha();
        $this->enableTwoFactorAuth();

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->method('redirect');
        Registry::set(Utils::class, $utilsMock);

        $subject = oxNew(User::class);
        $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $sessionUserName = Registry::getSession()->getVariable(AuthorizeService::USER_SESSION_KEY);
        $this->assertEquals(self::OTP_USER_NAME, $sessionUserName);
    }

    private function enableTwoFactorAuth(): void
    {
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        $moduleSettingService->saveBoolean(
            ModuleSettingsService::ACTIVE,
            true,
            Module::MODULE_ID
        );
        $moduleSettingService->saveString(
            ModuleSettingsService::TWO_FACTOR_TYPE,
            'otp',
            Module::MODULE_ID
        );
    }

    private function disableTwoFactorAuth(): void
    {
        $moduleSettingService = ContainerFacade::get(ModuleSettingServiceInterface::class);
        $moduleSettingService->saveBoolean(
            ModuleSettingsService::ACTIVE,
            false,
            Module::MODULE_ID
        );
    }

    private function disableCaptcha(): void
    {
        $captchaSettings = ContainerFacade::get(CaptchaSettingsServiceInterface::class);
        $captchaSettings->saveIsCaptchaEnabled(false);
    }
}
