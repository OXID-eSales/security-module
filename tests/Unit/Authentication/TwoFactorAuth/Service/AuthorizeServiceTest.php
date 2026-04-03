<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\VerificatorNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\NotifierAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\NotifierCollectorInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettingsInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ResendOTPServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\VerificationCollectorServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\VerificatorAdapterInterface;
use PHPUnit\Framework\TestCase;

class AuthorizeServiceTest extends TestCase
{
    public function testValidateThrowsWhenVerificatorNotFound(): void
    {
        $settingsStub = $this->createStub(TwoFASettingsInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('unknown');

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn(uniqid());

        $collectorStub = $this->createStub(VerificationCollectorServiceInterface::class);
        $collectorStub
            ->method('getVerificator')
            ->willThrowException(new VerificatorNotFoundException());

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorStub,
            session: $sessionStub
        );

        $this->expectException(VerificatorNotFoundException::class);

        $sut->validate(uniqid());
    }

    public function testValidateBubblesVerificatorException(): void
    {
        $exception = new InvalidCodeException();

        $verificatorStub = $this->createStub(VerificatorAdapterInterface::class);
        $verificatorStub
            ->method('validateCode')
            ->willThrowException($exception);

        $settingsStub = $this->createStub(TwoFASettingsInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('otp');

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn(uniqid());

        $collectorStub = $this->createStub(VerificationCollectorServiceInterface::class);
        $collectorStub
            ->method('getVerificator')
            ->willReturn($verificatorStub);

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorStub,
            session: $sessionStub
        );

        $this->expectExceptionObject($exception);

        $sut->validate(uniqid());
    }

    public function testValidateWithValidCodeDoesNotThrow(): void
    {
        $userId = uniqid();
        $code = uniqid();

        $verificatorMock = $this->createMock(VerificatorAdapterInterface::class);
        $verificatorMock
            ->expects($this->once())
            ->method('validateCode')
            ->with($userId, $code);

        $settingsStub = $this->createStub(TwoFASettingsInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('otp');

        $collectorStub = $this->createStub(VerificationCollectorServiceInterface::class);
        $collectorStub
            ->method('getVerificator')
            ->willReturn($verificatorMock);

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn($userId);

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorStub,
            session: $sessionStub
        );

        $sut->validate($code);

        $this->addToAssertionCount(1);
    }

    public function testGenerateNotifiesUser(): void
    {
        $generatedCode = uniqid();
        $userEmail = 'user@example.com';

        $verificatorStub = $this->createStub(VerificatorAdapterInterface::class);
        $verificatorStub
            ->method('generate')
            ->willReturn($generatedCode);

        $notifierMock = $this->createMock(NotifierAdapterInterface::class);
        $notifierMock
            ->expects($this->once())
            ->method('notify')
            ->with($userEmail, $generatedCode);

        $userStub = $this->createStub(UserInterface::class);
        $userStub->method('getEmail')->willReturn($userEmail);

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($userStub);

        $settingsStub = $this->createStub(TwoFASettingsInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('otp');

        $collectorStub = $this->createStub(VerificationCollectorServiceInterface::class);
        $collectorStub
            ->method('getVerificator')
            ->willReturn($verificatorStub);

        $notifierCollectorStub = $this->createStub(NotifierCollectorInterface::class);
        $notifierCollectorStub
            ->method('getNotifier')
            ->willReturn($notifierMock);

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn(uniqid());

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorStub,
            notifierCollector: $notifierCollectorStub,
            userRepository: $userRepositoryStub,
            session: $sessionStub
        );

        $sut->generate();
    }

    public function testResendSendsOtpAndMarksAsSent(): void
    {
        $userEmail = 'user@example.com';

        $verificatorStub = $this->createStub(VerificatorAdapterInterface::class);
        $verificatorStub->method('generate')->willReturn('123456');

        $notifierMock = $this->createMock(NotifierAdapterInterface::class);
        $notifierMock->expects($this->once())->method('notify');

        $userStub = $this->createStub(UserInterface::class);
        $userStub->method('getEmail')->willReturn($userEmail);

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($userStub);

        $settingsStub = $this->createStub(TwoFASettingsInterface::class);
        $settingsStub->method('getTwoFactorAuthType')->willReturn('otp');

        $collectorStub = $this->createStub(VerificationCollectorServiceInterface::class);
        $collectorStub->method('getVerificator')->willReturn($verificatorStub);

        $notifierCollectorStub = $this->createStub(NotifierCollectorInterface::class);
        $notifierCollectorStub->method('getNotifier')->willReturn($notifierMock);

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')->willReturn(uniqid());

        $resendOTPMock = $this->createMock(ResendOTPServiceInterface::class);
        $resendOTPMock->method('canSend')->willReturn(true);
        $resendOTPMock->expects($this->once())->method('markAsSent');

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorStub,
            notifierCollector: $notifierCollectorStub,
            resendOTPService: $resendOTPMock,
            userRepository: $userRepositoryStub,
            session: $sessionStub
        );

        $this->assertTrue($sut->resend());
    }

    public function testResendReturnsFalseWhenCooldownActive(): void
    {
        $resendOTPMock = $this->createMock(ResendOTPServiceInterface::class);
        $resendOTPMock
            ->method('canSend')
            ->willReturn(false);
        $resendOTPMock
            ->expects($this->never())
            ->method('markAsSent');

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn(uniqid());

        $sut = $this->getSut(
            resendOTPService: $resendOTPMock,
            session: $sessionStub
        );

        $this->assertFalse($sut->resend());
    }

    public function testGetRemainingAttemptsReturnsValueFromVerificator(): void
    {
        $userId = uniqid();
        $remainingAttempts = 3;

        $verificatorStub = $this->createStub(VerificatorAdapterInterface::class);
        $verificatorStub
            ->method('getRemainingAttempts')
            ->with($userId)
            ->willReturn($remainingAttempts);

        $settingsStub = $this->createStub(TwoFASettingsInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('otp');

        $collectorStub = $this->createStub(VerificationCollectorServiceInterface::class);
        $collectorStub
            ->method('getVerificator')
            ->willReturn($verificatorStub);

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->with(AuthorizeService::USER_SESSION_KEY)
            ->willReturn($userId);

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorStub,
            session: $sessionStub
        );

        $this->assertSame($remainingAttempts, $sut->getRemainingAttempts());
    }

    protected function getSut(
        TwoFASettingsInterface $moduleSettings = null,
        VerificationCollectorServiceInterface $verifyCollector = null,
        NotifierCollectorInterface $notifierCollector = null,
        ResendOTPServiceInterface $resendOTPService = null,
        UserRepositoryInterface $userRepository = null,
        SessionInterface $session = null
    ): AuthorizeService {
        return new AuthorizeService(
            moduleSettings: $moduleSettings ?? $this->createStub(TwoFASettingsInterface::class),
            verifyCollector: $verifyCollector ?? $this->createStub(VerificationCollectorServiceInterface::class),
            notifierCollector: $notifierCollector ?? $this->createStub(NotifierCollectorInterface::class),
            resendOTPService: $resendOTPService ?? $this->createStub(ResendOTPServiceInterface::class),
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class),
            session: $session ?? $this->createStub(SessionInterface::class),
        );
    }
}
