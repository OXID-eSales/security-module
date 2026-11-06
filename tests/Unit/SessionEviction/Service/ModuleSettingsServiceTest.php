<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\SessionEviction\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModuleSettingsServiceTest extends TestCase
{
    #[Test]
    #[DataProvider('gettersDataProvider')]
    public function returnsConfiguredBooleanModuleSetting(string $method, string $key, bool $settingValue): void
    {
        $settingService = $this->createMock(ModuleSettingServiceInterface::class);
        $settingService->method('getBoolean')
            ->with($key, Module::MODULE_ID)
            ->willReturn($settingValue);

        $sut = $this->getSut(moduleSettingService: $settingService);

        $this->assertSame($settingValue, $sut->$method());
    }

    public static function gettersDataProvider(): array
    {
        return [
            'isEnabled returns true' => [
                'method'    => 'isEnabled',
                'key'       => ModuleSettingsService::SESSION_EVICTION_ENABLE,
                'settingValue' => true,
            ],
            'isEnabled returns false' => [
                'method'    => 'isEnabled',
                'key'       => ModuleSettingsService::SESSION_EVICTION_ENABLE,
                'settingValue' => false,
            ],
            'isStrict returns true' => [
                'method'    => 'isStrict',
                'key'       => ModuleSettingsService::SESSION_EVICTION_STRICT,
                'settingValue' => true,
            ],
            'isStrict returns false' => [
                'method'    => 'isStrict',
                'key'       => ModuleSettingsService::SESSION_EVICTION_STRICT,
                'settingValue' => false,
            ],
        ];
    }

    private function getSut(
        ModuleSettingServiceInterface $moduleSettingService = null,
    ): ModuleSettingsServiceInterface {
        return new ModuleSettingsService(
            moduleSettingService: $moduleSettingService
                ?? $this->createStub(ModuleSettingServiceInterface::class),
        );
    }
}
