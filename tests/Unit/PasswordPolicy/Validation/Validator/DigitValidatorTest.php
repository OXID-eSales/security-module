<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\CharacterAnalysis;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\DigitValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DigitValidatorTest extends TestCase
{
    #[DataProvider('dataProviderDigitPasswordThrowException')]
    public function testValidationThrowException($password): void
    {
        $this->expectException(PasswordValidate::class);

        $digitValidator = $this->createValidator();
        $digitValidator->validate($password);
    }

    public static function dataProviderDigitPasswordThrowException(): array
    {
        return [
            [''],
            ['SomeLongPassword'],
        ];
    }

    #[DataProvider('dataProviderDigitPassword')]
    public function testValidation($password): void
    {
        $digitValidator = $this->createValidator();
        $digitValidator->validate($password);

        $this->addToAssertionCount(1);
    }

    public static function dataProviderDigitPassword(): array
    {
        return [
            ['1password'],
            ['passw0rd'],
            ['password_9'],
        ];
    }

    public function testValidationWithDisabledSetting(): void
    {
        $digitValidator = $this->createValidator(false);
        $digitValidator->validate('noDigitPassword');

        $this->addToAssertionCount(1);
    }

    private function createValidator(
        bool $settingValue = true
    ) {
        $settingService = $this->createMock(ModuleSettingInterface::class);
        $settingService->method('getPasswordDigit')->willReturn($settingValue);

        return new DigitValidator(
            $settingService,
            new CharacterAnalysis()
        );
    }
}
