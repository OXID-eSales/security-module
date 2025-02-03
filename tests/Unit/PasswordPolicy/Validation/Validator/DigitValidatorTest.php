<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordDigitException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\StringAnalysisServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\DigitValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DigitValidatorTest extends TestCase
{
    public function testValidationThrowsExceptionOnWrongStrings(): void
    {
        $sut = $this->getSut(
            stringAnalysisService: $stringAnalysisMock = $this->createMock(StringAnalysisServiceInterface::class),
        );

        $password = uniqid();
        $stringAnalysisMock->method('hasUpperCaseCharacter')->with($password)->willReturn(false);

        $this->expectException(PasswordDigitException::class);
        $sut->validate($password);
    }

    public function testValidationThrowsExceptionOnCorrectStrings(): void
    {
        $sut = $this->getSut(
            stringAnalysisService: $stringAnalysisMock = $this->createMock(StringAnalysisServiceInterface::class),
        );

        $password = uniqid();
        $stringAnalysisMock->method('hasDigitCharacter')->with($password)->willReturn(true);

        $sut->validate($password);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('boolDataProvider')]
    public function testValidationWithDisabledSetting(bool $settingValue, bool $expectedValue): void
    {
        $sut = $this->getSut(
            moduleSetting: $this->createConfiguredStub(ModuleSettingsServiceInterface::class, [
                'getPasswordDigit' => $settingValue
            ])
        );

        $this->assertSame($expectedValue, $sut->isEnabled());
    }

    public static function boolDataProvider(): \Generator
    {
        yield 'setting enabled' => [
            'settingValue' => true,
            'expectedValue' => true,
        ];

        yield 'setting disabled' => [
            'settingValue' => false,
            'expectedValue' => false,
        ];
    }

    private function getSut(
        ModuleSettingsServiceInterface $moduleSetting = null,
        StringAnalysisServiceInterface $stringAnalysisService = null,
    ): DigitValidator {
        return new DigitValidator(
            moduleSetting: $moduleSetting ?? $this->createStub(ModuleSettingsServiceInterface::class),
            strAnalysisService: $stringAnalysisService ?? $this->createStub(StringAnalysisServiceInterface::class),
        );
    }
}
