<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsService;
use OxidEsales\SecurityModule\Core\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class ModuleSettingsTest extends TestCase
{
    public static function gettersDataProvider(): array
    {
        return [
            self::prepareBooleanSetting('isFacebookActive', ModuleSettingsService::FACEBOOK_ACTIVE, true),
            self::prepareBooleanSetting('isFacebookActive', ModuleSettingsService::FACEBOOK_ACTIVE, false),

            self::prepareStringTestItem('getFacebookClientId', ModuleSettingsService::FACEBOOK_CLIENT_ID),
            self::prepareStringTestItem('getFacebookClientSecret', ModuleSettingsService::FACEBOOK_CLIENT_SECRET),
            self::prepareStringTestItem('getFacebookRedirectUrl', ModuleSettingsService::FACEBOOK_REDIRECT_URL),
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

        $sut = new ModuleSettingsService($mssMock);
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
}
