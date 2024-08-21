<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\MinimumLengthValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MinimumLengthValidatorTest extends TestCase
{
    #[DataProvider('dataProviderMinimumPasswordThrowException')]
    public function testValidationThrowException($password): void
    {
        $this->expectException(PasswordValidate::class);

        $minimumLengthValidator = $this->createValidator();
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
        $minimumLengthValidator = $this->createValidator();
        $minimumLengthValidator->validate('someLongPassword');

        $this->addToAssertionCount(1);
    }

    public function testValidationWithDisabledSetting(): void
    {
        $digitValidator = $this->createValidator(0);
        $digitValidator->validate('noDigitPassword');

        $this->addToAssertionCount(1);
    }

    private function createValidator(
        int $minimumLength = 8,
    ) {
        $settingService = $this->createMock(ModuleSettingInterface::class);
        $settingService->method('getPasswordMinimumLength')->willReturn($minimumLength);

        return new MinimumLengthValidator(
            $settingService
        );
    }
}
