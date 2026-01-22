<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsTest extends TestCase
{
    #[DataProvider('gettersDataProvider')]
    public function testGetters($method, $systemMethod, $key, $mockValue, $expectedValue): void
    {
        $sut = $this->getSut(
            moduleSettingService: $settingServiceStub = $this->createStub(ModuleSettingServiceInterface::class)
        );

        $settingServiceStub->method($systemMethod)
            ->with($key, Module::MODULE_ID)
            ->willReturn($mockValue);

        $this->assertEquals($expectedValue, $sut->$method());
    }

    public static function gettersDataProvider(): array
    {
        return [
            self::prepareBooleanSetting('isTwoFactorAuthEnabled', ModuleSettingsService::ACTIVE, true),
            self::prepareBooleanSetting('isTwoFactorAuthEnabled', ModuleSettingsService::ACTIVE, false),

            self::prepareStringTestItem('getTwoFactorAuthType', ModuleSettingsService::TWO_FACTOR_TYPE),
        ];
    }

    private static function prepareBooleanSetting(
        string $method,
        string $key,
        bool $value
    ): array {
        return [
            'method'        => $method,
            'systemMethod'  => 'getBoolean',
            'key'           => $key,
            'mockValue'     => $value,
            'expectedValue' => $value
        ];
    }

    private static function prepareStringTestItem(
        string $method,
        string $key
    ): array {
        $exampleValue = 'exampleValue';

        return [
            'method'        => $method,
            'systemMethod'  => 'getString',
            'key'           => $key,
            'mockValue'     => new UnicodeString($exampleValue),
            'expectedValue' => $exampleValue
        ];
    }

    public function getSut(
        ModuleSettingServiceInterface $moduleSettingService = null
    ): ModuleSettingsServiceInterface {
        return new ModuleSettingsService(
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingsServiceInterface::class),
        );
    }
}
