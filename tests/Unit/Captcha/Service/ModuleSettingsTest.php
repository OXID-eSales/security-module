<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsTest extends TestCase
{
    public function testGetTokenLifetimeDefault(): void
    {
        $moduleSettingBridgeMock = $this->getMockBuilder(ModuleSettingServiceInterface::class)->getMock();
        $moduleSettingBridgeMock->method('getString')->willReturn(new UnicodeString('asdf'));

        $moduleConfiguration = new ModuleSettingsService($moduleSettingBridgeMock);

        $this->assertSame('15M', $moduleConfiguration->getCaptchaLifeTime());
    }

    public function testGetTokenLifetime(): void
    {
        $moduleSettingBridgeMock = $this->getMockBuilder(ModuleSettingServiceInterface::class)->getMock();
        $moduleSettingBridgeMock->method('getString')->willReturn(new UnicodeString('30min'));

        $moduleConfiguration = new ModuleSettingsService($moduleSettingBridgeMock);

        $this->assertSame('30M', $moduleConfiguration->getCaptchaLifeTime());
    }
}
