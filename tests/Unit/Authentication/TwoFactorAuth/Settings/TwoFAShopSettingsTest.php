<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Settings;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettings;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettingsInterface;
use OxidEsales\SecurityModule\Core\Module;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class TwoFAShopSettingsTest extends TestCase
{
    #[Test]
    public function isTwoFactorAuthEnabledReturnsTrueWhenEnabled(): void
    {
        $moduleSettingServiceStub = $this->createStub(ModuleSettingServiceInterface::class);
        $moduleSettingServiceStub->method('getBoolean')
            ->with(TwoFAShopSettings::ACTIVE, Module::MODULE_ID)
            ->willReturn(true);

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceStub);

        $this->assertTrue($sut->isTwoFactorAuthEnabled());
    }

    #[Test]
    public function isTwoFactorAuthEnabledReturnsFalseWhenDisabled(): void
    {
        $moduleSettingServiceStub = $this->createStub(ModuleSettingServiceInterface::class);
        $moduleSettingServiceStub->method('getBoolean')
            ->with(TwoFAShopSettings::ACTIVE, Module::MODULE_ID)
            ->willReturn(false);

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceStub);

        $this->assertFalse($sut->isTwoFactorAuthEnabled());
    }

    #[Test]
    public function getTwoFactorAuthTypeReturnsConfiguredType(): void
    {
        $type = uniqid();
        $moduleSettingServiceStub = $this->createStub(ModuleSettingServiceInterface::class);
        $moduleSettingServiceStub->method('getString')
            ->with(TwoFAShopSettings::TWO_FACTOR_TYPE, Module::MODULE_ID)
            ->willReturn(new UnicodeString($type));

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceStub);

        $this->assertSame($type, $sut->getTwoFactorAuthType());
    }

    #[Test]
    public function getVerificationUrlReturnsVerificationControllerUrl(): void
    {
        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn($homeUrl = uniqid());

        $sut = $this->getSut(config: $configStub);

        $this->assertSame($homeUrl . 'cl=twofactorauth', $sut->getVerificationUrl());
    }

    #[Test]
    public function implementsInterface(): void
    {
        $this->assertInstanceOf(TwoFAShopSettingsInterface::class, $this->getSut());
    }

    private function getSut(
        Config $config = null,
        ModuleSettingServiceInterface $moduleSettingService = null,
    ): TwoFAShopSettings {
        return new TwoFAShopSettings(
            config: $config ?? $this->createStub(Config::class),
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingServiceInterface::class),
        );
    }
}
