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
use DateTimeImmutable;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettingsInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\SecurityModule\Shared\Model\User as SecurityModuleUser;
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

        $subject = $this->createUserMock(captchaEnabled: true, twoFaEnabled: false);
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

        $subject = $this->createUserMock(captchaEnabled: true, twoFaEnabled: false);
        $subject->checkValues('', '', '', [], []);
    }

    public function testLoginWithInvalidCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'invalid_captcha';
                }
                return null;
            });

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("ERROR_INVALID_CAPTCHA");

        $subject = $this->createUserMock(captchaEnabled: true, twoFaEnabled: false);
        $subject->login('', '');
    }

    public function testLoginWithEmptyCaptcha()
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return '';
                }
                return null;
            });

        $this->expectException(UserException::class);
        $this->expectExceptionMessage("ERROR_EMPTY_CAPTCHA");

        $subject = $this->createUserMock(captchaEnabled: true, twoFaEnabled: false);
        $subject->login('', '');
    }

    public function testLoginWithValidCaptchaAndValidCredentials(): void
    {
        $this->requestMock
            ->method('getRequestParameter')
            ->willReturnCallback(function ($param) {
                if ($param === 'captcha') {
                    return 'valid_captcha';
                }
                return null;
            });

        $subject = $this->createUserMock(captchaEnabled: true, twoFaEnabled: false);
        $result = $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $this->assertTrue($result);
    }

    public function testLoginWithOTPEnabledAndValidCredentialsRedirectsToOTP(): void
    {
        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->once())->method('redirect');
        Registry::set(Utils::class, $utilsMock);

        $subject = $this->createUserMock(captchaEnabled: false, twoFaEnabled: true);
        $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $this->assertNotNull(
            Registry::getSession()->getVariable(TwoFAUserService::USER_SESSION_KEY)
        );
    }

    public function testLoginWithOTPEnabledAndInvalidCredentialsThrowsException(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('ERROR_MESSAGE_USER_NOVALIDLOGIN');

        $subject = $this->createUserMock(captchaEnabled: false, twoFaEnabled: true);
        $subject->login(self::OTP_USER_NAME, uniqid());
    }

    public function testLoginWithOTPEnabledAndNonExistentUserThrowsException(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('ERROR_MESSAGE_USER_NOVALIDLOGIN');

        $subject = $this->createUserMock(captchaEnabled: false, twoFaEnabled: true);
        $subject->login('nonexistent@test.com', 'anypassword');
    }

    public function testLoginWithOTPDisabledCallsParentLogin(): void
    {
        $subject = $this->createUserMock(captchaEnabled: false, twoFaEnabled: false);
        $result = $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $this->assertTrue($result);
        $this->assertNull(
            Registry::getSession()->getVariable(TwoFAUserService::USER_SESSION_KEY)
        );
    }

    public function testLoginWithOTPEnabledStoresUserIdInSession(): void
    {
        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->method('redirect');
        Registry::set(Utils::class, $utilsMock);

        $subject = $this->createUserMock(captchaEnabled: false, twoFaEnabled: true);
        $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $sessionUserId = Registry::getSession()->getVariable(TwoFAUserService::USER_SESSION_KEY);
        $this->assertNotNull($sessionUserId);
        $this->assertEquals($subject->getId(), $sessionUserId);
    }

    public function testLoginWithVerifiedChallengeStateSkipsOTPRedirect(): void
    {
        $userId = $this->getOTPUserId();

        $stateRepo = $this->get(OtpChallengeStateRepositoryInterface::class);
        $stateRepo->createChallengeState($userId, 'hash', new DateTimeImmutable('+5 minutes'));
        $stateRepo->markVerified($userId);

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->expects($this->never())->method('redirect');
        Registry::set(Utils::class, $utilsMock);

        $subject = $this->createUserMock(captchaEnabled: false, twoFaEnabled: true);
        $result = $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $this->assertTrue($result);
    }

    public function testLoginWithOTPPassSessionVariableMismatchTriggersOTPFlow(): void
    {
        $mismatchedUserId = 'different-user-id';
        Registry::getSession()->setVariable('OTP_PASS', $mismatchedUserId);

        $utilsMock = $this->createMock(Utils::class);
        $utilsMock->method('redirect');
        Registry::set(Utils::class, $utilsMock);

        $subject = $this->createUserMock(captchaEnabled: false, twoFaEnabled: true);
        $subject->login(self::OTP_USER_NAME, self::OTP_USER_PASSWORD);

        $this->assertNotNull(
            Registry::getSession()->getVariable(TwoFAUserService::USER_SESSION_KEY),
            'USER_SESSION_KEY should be set when OTP flow is triggered'
        );
        $this->assertSame(
            $mismatchedUserId,
            Registry::getSession()->getVariable('OTP_PASS'),
            'OTP_PASS should NOT be cleared when user ID does not match'
        );
    }

    private function createUserMock(bool $captchaEnabled, bool $twoFaEnabled): SecurityModuleUser
    {
        $captchaSettings = $this->createMock(CaptchaSettingsServiceInterface::class);
        $captchaSettings->method('isCaptchaEnabled')->willReturn($captchaEnabled);
        $captchaSettings->method('isHoneyPotCaptchaEnabled')->willReturn(false);

        $twoFaSettings = $this->createMock(TwoFASettingsInterface::class);
        $twoFaSettings->method('isTwoFactorAuthEnabled')->willReturn($twoFaEnabled);

        $serviceMocks = [
            CaptchaSettingsServiceInterface::class => $captchaSettings,
            TwoFASettingsInterface::class => $twoFaSettings,
        ];

        /** @var SecurityModuleUser $userMock */
        $userMock = $this->getMockBuilder(SecurityModuleUser::class)
            ->onlyMethods(['getService'])
            ->getMock();
        $userMock->method('getService')->willReturnCallback(
            fn(string $id) => $serviceMocks[$id] ?? ContainerFacade::get($id)
        );

        return $userMock;
    }

    private function getOTPUserId(): string
    {
        $user = oxNew(User::class);
        $user->load($user->getIdByUserName(self::OTP_USER_NAME));
        return $user->getId();
    }
}
