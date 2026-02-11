<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Core\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsServiceTest extends TestCase
{
    public static function gettersDataProvider(): array
    {
        return [
            self::prepareBooleanSetting('isTwoFactorAuthEnabled', ModuleSettingsService::ACTIVE, true),
            self::prepareBooleanSetting('isTwoFactorAuthEnabled', ModuleSettingsService::ACTIVE, false),
            self::prepareStringSetting('getTwoFactorAuthType', ModuleSettingsService::TWO_FACTOR_TYPE),
        ];
    }

    #[DataProvider('gettersDataProvider')]
    public function testGetters(
        string $method,
        string $systemMethod,
        string $key,
        mixed $mockValue,
        mixed $expectedValue
    ): void {
        $mssMock = $this->createPartialMock(ModuleSettingService::class, [$systemMethod]);
        $mssMock->expects($this->once())
            ->method($systemMethod)
            ->with($key, Module::MODULE_ID)
            ->willReturn($mockValue);

        $sut = new ModuleSettingsService($mssMock);

        $this->assertSame($expectedValue, $sut->$method());
    }

    public static function saveBooleanDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    #[DataProvider('saveBooleanDataProvider')]
    public function testSaveIsTwoFactorAuthEnabled(bool $value): void
    {
        $mssMock = $this->createPartialMock(ModuleSettingService::class, ['saveBoolean']);
        $mssMock->expects($this->once())
            ->method('saveBoolean')
            ->with(ModuleSettingsService::ACTIVE, $value, Module::MODULE_ID);

        $sut = new ModuleSettingsService($mssMock);
        $sut->saveIsTwoFactorAuthEnabled($value);
    }

    private static function prepareBooleanSetting(string $method, string $key, bool $value): array
    {
        return [
            'method' => $method,
            'systemMethod' => 'getBoolean',
            'key' => $key,
            'mockValue' => $value,
            'expectedValue' => $value,
        ];
    }

    private static function prepareStringSetting(string $method, string $key): array
    {
        $exampleValue = uniqid();
        return [
            'method' => $method,
            'systemMethod' => 'getString',
            'key' => $key,
            'mockValue' => new UnicodeString($exampleValue),
            'expectedValue' => $exampleValue,
        ];
    }
}
