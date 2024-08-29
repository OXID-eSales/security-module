<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordSpecialCharException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\CharacterAnalysis;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\SpecialCharValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SpecialCharValidatorTest extends TestCase
{
    #[DataProvider('dataProviderSpecialCharPasswordThrowException')]
    public function testValidationThrowException($password): void
    {
        $this->expectException(PasswordSpecialCharException::class);

        $specialCharValidator = $this->createValidator();
        $specialCharValidator->validate($password);
    }

    public static function dataProviderSpecialCharPasswordThrowException(): array
    {
        return [
            [''],
            ['123456789'],
            ['SomeLongPassword'],
            ['SomeLongPassword1'],
        ];
    }

    #[DataProvider('dataProviderSpecialCharPassword')]
    public function testValidation($password): void
    {
        $specialCharValidator = $this->createValidator();
        $specialCharValidator->validate($password);

        $this->addToAssertionCount(1);
    }

    public static function dataProviderSpecialCharPassword(): array
    {
        return [
            //special chars ASCII between 32 and 47
            ['#specialChar'],
            ['special!Char'],
            ['special&longPassword'],

            //special chars ASCII between 58 and 64
            ['<specialChar'],
            ['special@Char'],
            ['specialLongPassword>'],

            //special chars ASCII between 91 and 96
            ['[specialChar'],
            ['special[]Char'],
            ['specialLongPassword]'],

            //special chars ASCII above 128
            ['äöü'],
            ['{}'],
        ];
    }

    public function testValidationWithDisabledSetting(): void
    {
        $digitValidator = $this->createValidator(false);
        $digitValidator->validate('noSpecialCharPassword');

        $this->addToAssertionCount(1);
    }

    private function createValidator(
        bool $settingValue = true
    ) {
        $settingService = $this->createMock(ModuleSettingInterface::class);
        $settingService->method('getPasswordSpecialChar')->willReturn($settingValue);

        return new SpecialCharValidator(
            $settingService,
            new CharacterAnalysis()
        );
    }
}
