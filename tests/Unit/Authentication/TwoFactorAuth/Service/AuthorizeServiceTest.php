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
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\VerificationCollectorServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\VerificatorAdapterInterface;
use PHPUnit\Framework\TestCase;

class AuthorizeServiceTest extends TestCase
{
    private ModuleSettingsServiceInterface $settings;
    private VerificationCollectorServiceInterface $collector;
    private NotifierCollectorInterface $notifierCollector;
    private SessionInterface $session;

    protected function setUp(): void
    {
        $this->settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $this->collector = $this->createMock(VerificationCollectorServiceInterface::class);
        $this->notifierCollector = $this->createMock(NotifierCollectorInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
    }

    public function testValidateThrowsWhenVerificatorNotFound(): void
    {
        $this->settings->method('getTwoFactorAuthType')->willReturn('unknown');
        $this->collector
            ->method('getVerificator')
            ->with('unknown')
            ->willThrowException(new VerificatorNotFoundException());

        $this->session->method('get')->willReturn(uniqid());

        $this->expectException(VerificatorNotFoundException::class);

        $service = new AuthorizeService(
            $this->settings,
            $this->collector,
            $this->createMock(NotifierCollectorInterface::class),
            $this->session
        );

        $service->validate(uniqid());
    }

    public function testValidateBubblesVerificatorException(): void
    {
        $exception = new InvalidCodeException();

        $verificator = $this->createMock(VerificatorAdapterInterface::class);
        $verificator
            ->method('validateCode')
            ->willThrowException($exception);

        $this->settings->method('getTwoFactorAuthType')->willReturn('otp');
        $this->collector->method('getVerificator')->willReturn($verificator);
        $this->session->method('get')->willReturn(uniqid());

        $this->expectExceptionObject($exception);

        $service = new AuthorizeService(
            $this->settings,
            $this->collector,
            $this->createMock(NotifierCollectorInterface::class),
            $this->session
        );

        $service->validate(uniqid());
    }

    public function testValidateWithValidCodeDoesNotThrow(): void
    {
        $verificator = $this->createMock(VerificatorAdapterInterface::class);
        $verificator
            ->expects($this->once())
            ->method('validateCode')
            ->with($email = uniqid(), $code = uniqid());

        $settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $collector = $this->createMock(VerificationCollectorServiceInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $settings->method('getTwoFactorAuthType')->willReturn('otp');
        $collector->method('getVerificator')->willReturn($verificator);
        $session->method('get')->willReturn($email);

        $service = new AuthorizeService(
            $settings,
            $collector,
            $this->createMock(NotifierCollectorInterface::class),
            $session
        );

        $service->validate($code);
        $this->addToAssertionCount(1);
    }

    public function testGenerateNotifiesUser(): void
    {
        $verificator = $this->createMock(VerificatorAdapterInterface::class);
        $verificator->method('generate')->willReturn(uniqid());

        $notifier = $this->createMock(NotifierAdapterInterface::class);
        $notifier->expects($this->once())->method('notify');

        $this->settings->method('getTwoFactorAuthType')->willReturn('otp');
        $this->collector->method('getVerificator')->willReturn($verificator);
        $this->notifierCollector->method('getNotifier')->willReturn($notifier);
        $this->session->method('get')->willReturn(uniqid());

        $service = new AuthorizeService(
            $this->settings,
            $this->collector,
            $this->notifierCollector,
            $this->session
        );

        $service->generate();
    }
}
