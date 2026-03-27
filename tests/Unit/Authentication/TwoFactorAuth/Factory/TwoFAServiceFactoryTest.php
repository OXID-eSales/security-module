<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Factory;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Factory\TwoFAServiceFactory;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Factory\TwoFAServiceFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AuthenticationTypeNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use PHPUnit\Framework\TestCase;

class TwoFAServiceFactoryTest extends TestCase
{
    public function testCreateReturnsServiceMatchingConfiguredType(): void
    {
        $otpServiceStub = $this->createStub(TwoFAServiceInterface::class);
        $totpServiceStub = $this->createStub(TwoFAServiceInterface::class);

        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('getTwoFactorAuthType')->willReturn('otp');

        $sut = $this->getSut(
            settings: $settingsStub,
            implementations: ['otp' => $otpServiceStub, 'totp' => $totpServiceStub],
        );

        $this->assertSame($otpServiceStub, $sut->create());
    }

    public function testCreateThrowsForUnknownType(): void
    {
        $settingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsStub->method('getTwoFactorAuthType')->willReturn('unknown');

        $sut = $this->getSut(
            settings: $settingsStub,
        );

        $this->expectException(AuthenticationTypeNotFoundException::class);
        $sut->create();
    }

    private function getSut(
        ModuleSettingsServiceInterface $settings = null,
        array $implementations = [],
    ): TwoFAServiceFactoryInterface {
        return new TwoFAServiceFactory(
            settings: $settings ?? $this->createStub(ModuleSettingsServiceInterface::class),
            implementations: $implementations,
        );
    }
}
