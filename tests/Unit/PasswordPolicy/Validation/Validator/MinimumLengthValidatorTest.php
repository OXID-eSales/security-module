<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\Eshop\Core\InputValidator;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordMinimumLengthException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\MinimumLengthValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MinimumLengthValidatorTest extends TestCase
{
    #[DataProvider('dataProviderMinimumPasswordThrowException')]
    public function testValidationThrowException($password): void
    {
        $minimumLengthValidator = $this->getSut(
            moduleSetting: $this->createConfiguredStub(ModuleSettingsServiceInterface::class, [
                'getPasswordMinimumLength' => 8
            ])
        );

        $this->expectException(PasswordMinimumLengthException::class);
        $minimumLengthValidator->validate($password);
    }

    public static function dataProviderMinimumPasswordThrowException(): array
    {
        return [
            [''],
            ['pass'],
            ['maximum'],
        ];
    }

    public function testValidation(): void
    {
        $minimumLengthValidator = $this->getSut();

        $minimumLengthValidator->validate('someLongPassword');
        $this->addToAssertionCount(1);
    }

    #[DataProvider('boolDataProvider')]
    public function testValidationWithDisabledSetting(int $settingValue, bool $expectedValue): void
    {
        $sut = $this->getSut(
            moduleSetting: $this->createConfiguredStub(ModuleSettingsServiceInterface::class, [
                'getPasswordMinimumLength' => $settingValue
            ])
        );

        $this->assertSame($expectedValue, $sut->isEnabled());
    }

    public function testValidationUseShopMinimumLength(): void
    {
        $minimumLengthValidator = $this->getSut(
            moduleSetting: $this->createConfiguredStub(ModuleSettingsServiceInterface::class, [
                'getPasswordMinimumLength' => 0
            ])
        );

        $this->expectException(PasswordMinimumLengthException::class);
        $minimumLengthValidator->validate('short');
    }

    public static function boolDataProvider(): \Generator
    {
        yield 'setting enabled' => [
            'settingValue'  => 8,
            'expectedValue' => true,
        ];

        yield 'setting disabled' => [
            'settingValue'  => 0,
            'expectedValue' => false,
        ];
    }

    private function getSut(
        ModuleSettingsServiceInterface $moduleSetting = null,
    ): MinimumLengthValidator {
        return new MinimumLengthValidator(
            moduleSetting: $moduleSetting ?? $this->createConfiguredStub(ModuleSettingsServiceInterface::class, [
                'getPasswordMinimumLength' => 8
            ]),
            inputValidator: $this->createConfiguredStub(InputValidator::class, [
                'getPasswordLength' => 6
            ])
        );
    }
}
