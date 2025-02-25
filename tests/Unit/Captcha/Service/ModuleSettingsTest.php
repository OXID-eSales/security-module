<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Core\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsTest extends TestCase
{
    #[DataProvider('dataProviderValidate')]
    public function testGetTokenLifetime($value, $expectedValue): void
    {
        $moduleSettingBridgeMock = $this->getMockBuilder(ModuleSettingServiceInterface::class)->getMock();
        $moduleSettingBridgeMock->method('getString')->willReturn(new UnicodeString($value));

        $moduleConfiguration = new ModuleSettingsService($moduleSettingBridgeMock);

        $this->assertSame($expectedValue, $moduleConfiguration->getCaptchaLifeTime());
    }

    public static function dataProviderValidate(): \Generator
    {
        yield 'invalid expire time' => [
            'value' => uniqid(),
            'expectedValue' => '15M',
        ];

        yield 'invalid expire time value' => [
            'value' => '60min',
            'expectedValue' => '15M',
        ];

        yield 'valid expire time value' => [
            'value' => '15min',
            'expectedValue' => '15M',
        ];
    }

    #[DataProvider('gettersDataProvider')]
    public function testGetters($method, $systemMethod, $key, $mockValue, $expectedValue): void
    {
        $sut = $this->getSut(
            moduleSettingService: $settingService = $this->createMock(ModuleSettingServiceInterface::class)
        );

        $settingService->method($systemMethod)
            ->with(
                $key,
                Module::MODULE_ID
            )
            ->willReturn($mockValue);

        $this->assertEquals($expectedValue, $sut->$method());
    }

    public static function gettersDataProvider(): array
    {
        return [
            self::prepareBooleanSetting(
                'isHoneyPotCaptchaEnabled',
                ModuleSettingsService::HONEYPOT_CAPTCHA_ENABLE,
                false
            ),
            self::prepareBooleanSetting(
                'isCaptchaEnabled',
                ModuleSettingsService::CAPTCHA_ENABLE,
                false
            ),
        ];
    }

    private static function prepareBooleanSetting(string $method, string $key, bool $value): array
    {
        return [
            'method'        => $method,
            'systemMethod'  => 'getBoolean',
            'key'           => $key,
            'mockValue'     => $value,
            'expectedValue' => $value
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
