<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\VerificatorNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\NotifierAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\NotifierCollectorInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ResendOTPServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\VerificationCollectorServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\VerificatorAdapterInterface;
use PHPUnit\Framework\TestCase;

class AuthorizeServiceTest extends TestCase
{
    public function testValidateThrowsWhenVerificatorNotFound(): void
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('unknown');

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn(uniqid());

        $collectorMock = $this->createMock(VerificationCollectorServiceInterface::class);
        $collectorMock
            ->method('getVerificator')
            ->with('unknown')
            ->willThrowException(new VerificatorNotFoundException());

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorMock,
            session: $sessionStub
        );

        $this->expectException(VerificatorNotFoundException::class);

        $sut->validate(uniqid());
    }

    public function testValidateBubblesVerificatorException(): void
    {
        $exception = new InvalidCodeException();

        $verificatorMock = $this->createMock(VerificatorAdapterInterface::class);
        $verificatorMock
            ->method('validateCode')
            ->willThrowException($exception);

        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('otp');

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn(uniqid());

        $collectorMock = $this->createMock(VerificationCollectorServiceInterface::class);
        $collectorMock
            ->method('getVerificator')
            ->with('otp')
            ->willReturn($verificatorMock);

        $sut = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorMock,
            session: $sessionStub
        );

        $this->expectExceptionObject($exception);

        $sut->validate(uniqid());
    }

    public function testValidateWithValidCodeDoesNotThrow(): void
    {
        $email = uniqid();
        $code = uniqid();

        $verificatorMock = $this->createMock(VerificatorAdapterInterface::class);
        $verificatorMock
            ->expects($this->once())
            ->method('validateCode')
            ->with($email, $code);

        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
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
            ->willReturn($email);

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

        $verificatorStub = $this->createStub(VerificatorAdapterInterface::class);
        $verificatorStub
            ->method('generate')
            ->willReturn($generatedCode);

        $notifierMock = $this->createMock(NotifierAdapterInterface::class);
        $notifierMock
            ->expects($this->once())
            ->method('notify');

        $resendOTPStub = $this->createStub(ResendOTPServiceInterface::class);
        $resendOTPStub->method('canSend')->willReturn(true);

        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
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
            resendOTPService: $resendOTPStub,
            session: $sessionStub
        );

        $sut->generate();
    }

    public function testGenerateDoesNothingWhenCannotSend(): void
    {
        $verificatorMock = $this->createMock(VerificatorAdapterInterface::class);
        $verificatorMock
            ->expects($this->never())
            ->method('generate');

        $notifierMock = $this->createMock(NotifierAdapterInterface::class);
        $notifierMock
            ->expects($this->never())
            ->method('notify');

        $resendOTPMock = $this->createMock(ResendOTPServiceInterface::class);
        $resendOTPMock
            ->method('canSend')
            ->willReturn(false);
        $resendOTPMock
            ->expects($this->never())
            ->method('markAsSent');

        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub
            ->method('getTwoFactorAuthType')
            ->willReturn('otp');

        $collectorStub = $this->createStub(VerificationCollectorServiceInterface::class);
        $collectorStub
            ->method('getVerificator')
            ->willReturn($verificatorMock);

        $notifierCollectorStub = $this->createStub(NotifierCollectorInterface::class);
        $notifierCollectorStub
            ->method('getNotifier')
            ->willReturn($notifierMock);

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub
            ->method('get')
            ->willReturn(uniqid());

        $service = $this->getSut(
            moduleSettings: $settingsStub,
            verifyCollector: $collectorStub,
            notifierCollector: $notifierCollectorStub,
            resendOTPService: $resendOTPMock,
            session: $sessionStub
        );
        $service->generate();
    }

    protected function getSut(
        ModuleSettingsServiceInterface $moduleSettings = null,
        VerificationCollectorServiceInterface $verifyCollector = null,
        NotifierCollectorInterface $notifierCollector = null,
        ResendOTPServiceInterface $resendOTPService = null,
        SessionInterface $session = null
    ): AuthorizeService {
        return new AuthorizeService(
            moduleSettings: $moduleSettings ?? $this->createStub(ModuleSettingsServiceInterface::class),
            verifyCollector: $verifyCollector ?? $this->createStub(VerificationCollectorServiceInterface::class),
            notifierCollector: $notifierCollector ?? $this->createStub(NotifierCollectorInterface::class),
            resendOTPService: $resendOTPService ?? $this->createStub(ResendOTPServiceInterface::class),
            session: $session ?? $this->createStub(SessionInterface::class),
        );
    }
}
