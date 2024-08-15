<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\LowerCaseValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LowerCaseValidatorTest extends TestCase
{
    #[DataProvider('dataProviderLowerCasePasswordThrowException')]
    public function testValidationThrowException($password): void
    {
        $this->expectException(PasswordValidate::class);

        $lowerCaseValidator = $this->createValidator();
        $lowerCaseValidator->validate($password);
    }

    public static function dataProviderLowerCasePasswordThrowException(): array
    {
        return [
            [''],
            ['123456789'],
            ['PASSWORD'],
            ['SOME_LONG_PASSWORD'],
            ['SOME_LONG_PASSWORD_1'],
        ];
    }

    #[DataProvider('dataProviderLowerCasePassword')]
    public function testValidation($password): void
    {
        $lowerCaseValidator = $this->createValidator();
        $lowerCaseValidator->validate($password);

        $this->addToAssertionCount(1);
    }

    public static function dataProviderLowerCasePassword(): array
    {
        return [
            ['SOME_LONG_pASSWORD'],
            ['SOME_lONG_PASSWORD'],
            ['sOME_LONG_PASSWORD'],
            ['sOME_lONG_pASSWORD'],
        ];
    }

    public function testValidationWithDisabledSetting(): void
    {
        $digitValidator = $this->createValidator(false);
        $digitValidator->validate('NO_LOWER_CASE_PASSWORD');

        $this->addToAssertionCount(1);
    }

    private function createValidator(
        bool $settingValue = true
    ) {
        $settingService = $this->createMock(ModuleSettingInterface::class);
        $settingService->method('getPasswordLowercase')->willReturn($settingValue);

        return new LowerCaseValidator(
            $settingService
        );
    }
}
