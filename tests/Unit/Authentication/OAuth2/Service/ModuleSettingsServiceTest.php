<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Core\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsServiceTest extends TestCase
{
    public static function gettersDataProvider(): array
    {
        return [
            self::prepareBooleanSetting('isFacebookLoginEnabled', ModuleSettingsService::FACEBOOK_LOGIN_ENABLED, true),
            self::prepareBooleanSetting('isFacebookLoginEnabled', ModuleSettingsService::FACEBOOK_LOGIN_ENABLED, false),
            self::prepareStringTestItem('getFacebookClientId', ModuleSettingsService::FACEBOOK_CLIENT_ID),
            self::prepareStringTestItem('getFacebookClientSecret', ModuleSettingsService::FACEBOOK_CLIENT_SECRET),

            self::prepareBooleanSetting('isGoogleLoginEnabled', ModuleSettingsService::GOOGLE_LOGIN_ENABLED, true),
            self::prepareBooleanSetting('isGoogleLoginEnabled', ModuleSettingsService::GOOGLE_LOGIN_ENABLED, false),
            self::prepareStringTestItem('getGoogleClientId', ModuleSettingsService::GOOGLE_CLIENT_ID),
            self::prepareStringTestItem('getGoogleClientSecret', ModuleSettingsService::GOOGLE_CLIENT_SECRET),
        ];
    }

    #[DataProvider('gettersDataProvider')]
    public function testGetters($method, $systemMethod, $key, $mockValue, $expectedValue)
    {
        $mssMock = $this->createPartialMock(ModuleSettingService::class, [$systemMethod]);
        $mssMock->expects($this->once())->method($systemMethod)->with(
            $key,
            Module::MODULE_ID
        )->willReturn($mockValue);

        $configMock = $this->createMock(Config::class);

        $sut = new ModuleSettingsService($mssMock, $configMock);
        $this->assertSame($expectedValue, $sut->$method());
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
        $exampleValue = uniqid();
        return [
            'method' => $method,
            'systemMethod' => 'getString',
            'key' => $key,
            'mockValue' => new UnicodeString($exampleValue),
            'expectedValue' => $exampleValue
        ];
    }

    public static function saveBooleanDataProvider(): array
    {
        return [
            ['saveFacebookEnabled', ModuleSettingsService::FACEBOOK_LOGIN_ENABLED, true],
            ['saveFacebookEnabled', ModuleSettingsService::FACEBOOK_LOGIN_ENABLED, false],
            ['saveGoogleEnabled', ModuleSettingsService::GOOGLE_LOGIN_ENABLED, true],
            ['saveGoogleEnabled', ModuleSettingsService::GOOGLE_LOGIN_ENABLED, false],
        ];
    }

    #[DataProvider('saveBooleanDataProvider')]
    public function testSaveBooleanSettings(string $method, string $key, bool $value): void
    {
        $mssMock = $this->createPartialMock(ModuleSettingService::class, ['saveBoolean']);
        $mssMock->expects($this->once())
            ->method('saveBoolean')
            ->with($key, $value, Module::MODULE_ID);

        $configMock = $this->createMock(Config::class);

        $sut = new ModuleSettingsService($mssMock, $configMock);
        $sut->$method($value);
    }

    public static function redirectUrlDataProvider(): array
    {
        return [
            'facebook' => [
                'method' => 'getFacebookRedirectUrl',
                'settingKey' => ModuleSettingsService::FACEBOOK_REDIRECT_URL,
                'provider' => 'facebook',
            ],
            'google' => [
                'method' => 'getGoogleRedirectUrl',
                'settingKey' => ModuleSettingsService::GOOGLE_REDIRECT_URL,
                'provider' => 'google',
            ],
        ];
    }

    #[DataProvider('redirectUrlDataProvider')]
    public function testRedirectUrlIsGeneratedFromConfig(
        string $method,
        string $settingKey,
        string $provider
    ): void {
        $shopUrl = 'https://myshop.com/';
        $expectedUrl = $shopUrl . 'index.php?cl=oauth&fnc=redirect&provider=' . $provider;

        $configMock = $this->createMock(Config::class);
        $configMock->method('getShopUrl')->willReturn($shopUrl);

        $mssMock = $this->createPartialMock(ModuleSettingService::class, ['getString', 'saveString']);
        $mssMock->method('getString')
            ->with($settingKey, Module::MODULE_ID)
            ->willReturn(new UnicodeString('old-value'));
        $mssMock->expects($this->once())
            ->method('saveString')
            ->with($settingKey, $expectedUrl, Module::MODULE_ID);

        $sut = new ModuleSettingsService($mssMock, $configMock);
        $this->assertSame($expectedUrl, $sut->$method());
    }

    #[DataProvider('redirectUrlDataProvider')]
    public function testRedirectUrlSkipsSaveWhenUnchanged(
        string $method,
        string $settingKey,
        string $provider
    ): void {
        $shopUrl = 'https://myshop.com/';
        $expectedUrl = $shopUrl . 'index.php?cl=oauth&fnc=redirect&provider=' . $provider;

        $configMock = $this->createMock(Config::class);
        $configMock->method('getShopUrl')->willReturn($shopUrl);

        $mssMock = $this->createPartialMock(ModuleSettingService::class, ['getString', 'saveString']);
        $mssMock->method('getString')
            ->with($settingKey, Module::MODULE_ID)
            ->willReturn(new UnicodeString($expectedUrl));
        $mssMock->expects($this->never())
            ->method('saveString');

        $sut = new ModuleSettingsService($mssMock, $configMock);
        $this->assertSame($expectedUrl, $sut->$method());
    }
}
