<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrength;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\UpperCaseValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UpperCaseValidatorTest extends TestCase
{
    #[DataProvider('dataProviderUpperCasePasswordThrowException')]
    public function testValidationThrowException($password): void
    {
        $this->expectException(PasswordValidate::class);

        $upperCaseValidator = $this->createValidator();
        $upperCaseValidator->validate($password);
    }

    public static function dataProviderUpperCasePasswordThrowException(): array
    {
        return [
            [''],
            ['123456789'],
            ['some_long_password'],
            ['some_long_password_1'],
        ];
    }

    #[DataProvider('dataProviderUpperCasePassword')]
    public function testValidation($password): void
    {
        $upperCaseValidator = $this->createValidator();
        $upperCaseValidator->validate($password);

        $this->addToAssertionCount(1);
    }

    public function testValidationWithDisabledSetting(): void
    {
        $digitValidator = $this->createValidator(false);
        $digitValidator->validate('no_upper_case_password');

        $this->addToAssertionCount(1);
    }

    public static function dataProviderUpperCasePassword(): array
    {
        return [
            ['some_long_Password'],
            ['some_Long_password'],
            ['Some_long_password'],
            ['Some_Long_Password'],
        ];
    }

    private function createValidator(
        bool $settingValue = true
    ) {
        $settingService = $this->createMock(ModuleSettingInterface::class);
        $settingService->method('getPasswordUppercase')->willReturn($settingValue);

        return new UpperCaseValidator(
            $settingService,
            new PasswordStrength()
        );
    }
}
