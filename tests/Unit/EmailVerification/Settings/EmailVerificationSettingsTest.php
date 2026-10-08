<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Settings;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettings;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmailVerificationSettingsTest extends TestCase
{
    #[Test]
    public function isEmailVerificationEnabled(): void
    {
        $moduleSettingServiceMock = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingServiceMock->method('getBoolean')
            ->with(EmailVerificationSettings::ACTIVE, Module::MODULE_ID)
            ->willReturn($enabled = (bool) rand(0, 1));

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceMock);

        $this->assertSame($enabled, $sut->isEmailVerificationEnabled());
    }

    #[Test]
    public function getLinkLifetimeHours(): void
    {
        $moduleSettingServiceMock = $this->createMock(ModuleSettingServiceInterface::class);
        $moduleSettingServiceMock->method('getInteger')
            ->with(EmailVerificationSettings::LINK_LIFETIME, Module::MODULE_ID)
            ->willReturn($hours = rand(EmailVerificationSettings::MIN_LINK_LIFETIME_HOURS, 720));

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceMock);

        $this->assertSame($hours, $sut->getLinkLifetimeHours());
    }

    #[Test]
    #[DataProvider('nonPositiveLifetimeDataProvider')]
    public function getLinkLifetimeHoursFallsBackToMinimumForNonPositiveValues(int $hours): void
    {
        $moduleSettingServiceStub = $this->createStub(ModuleSettingServiceInterface::class);
        $moduleSettingServiceStub->method('getInteger')->willReturn($hours);

        $sut = $this->getSut(moduleSettingService: $moduleSettingServiceStub);

        $this->assertSame(EmailVerificationSettings::MIN_LINK_LIFETIME_HOURS, $sut->getLinkLifetimeHours());
    }

    public static function nonPositiveLifetimeDataProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-rand(1, 100)],
        ];
    }

    private function getSut(
        ?ModuleSettingServiceInterface $moduleSettingService = null,
    ): EmailVerificationSettingsInterface {
        return new EmailVerificationSettings(
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingServiceInterface::class),
        );
    }
}
