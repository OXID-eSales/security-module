<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Settings;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettings;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettingsInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TwoFASettingsTest extends TestCase
{
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
        $this->assertInstanceOf(TwoFASettingsInterface::class, $this->getSut());
    }

    private function getSut(Config $config = null): TwoFASettings
    {
        return new TwoFASettings(
            config: $config ?? $this->createStub(Config::class),
        );
    }
}
