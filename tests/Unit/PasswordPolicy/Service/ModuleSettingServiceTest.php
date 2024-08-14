<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\PasswordPolicy\Service\Unit;

use OxidEsales\SecurityModule\Core\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSetting;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;

class ModuleSettingServiceTest extends TestCase
{
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
            self::prepareIntegerSetting('getPasswordMinimumLength', ModuleSetting::PASSWORD_MINIMUM_LENGTH),
            self::prepareIntegerSetting('getPasswordAcceptableLength', ModuleSetting::PASSWORD_ACCEPTABLE_LENGTH),
            self::prepareIntegerSetting('getPasswordPerfectLength', ModuleSetting::PASSWORD_PERFECT_LENGTH),

            self::prepareBooleanSetting('getPasswordUppercase', ModuleSetting::PASSWORD_UPPERCASE, true),
            self::prepareBooleanSetting('getPasswordUppercase', ModuleSetting::PASSWORD_UPPERCASE, false),
            self::prepareBooleanSetting('getPasswordLowercase', ModuleSetting::PASSWORD_LOWERCASE, true),
            self::prepareBooleanSetting('getPasswordLowercase', ModuleSetting::PASSWORD_LOWERCASE, false),
            self::prepareBooleanSetting('getPasswordDigit', ModuleSetting::PASSWORD_DIGIT, true),
            self::prepareBooleanSetting('getPasswordDigit', ModuleSetting::PASSWORD_DIGIT, false),
            self::prepareBooleanSetting('getPasswordSpecialChar', ModuleSetting::PASSWORD_SPECIAL_CHAR, true),
            self::prepareBooleanSetting('getPasswordSpecialChar', ModuleSetting::PASSWORD_SPECIAL_CHAR, false),
        ];
    }

    private static function prepareIntegerSetting(string $method, string $key): array
    {
        $value = rand();

        return [
            'method'        => $method,
            'systemMethod'  => 'getInteger',
            'key'           => $key,
            'mockValue'     => $value,
            'expectedValue' => $value
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
    ): ModuleSettingInterface {
        return new ModuleSetting(
            moduleSettingService: $moduleSettingService ?? $this->createStub(ModuleSettingInterface::class),
        );
    }
}
