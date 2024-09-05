<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Tests\Unit\PasswordPolicy\Validator\Validation;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordSpecialCharException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\StringAnalysisServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator\SpecialCharValidator;
use PHPUnit\Framework\TestCase;

class SpecialCharValidatorTest extends TestCase
{
    public function testValidationThrowsExceptionOnWrongStrings(): void
    {
        $sut = $this->getSut(
            stringAnalysisService: $stringAnalysisMock = $this->createMock(StringAnalysisServiceInterface::class),
        );

        $password = uniqid();
        $stringAnalysisMock->method('hasSpecialCharacter')->with($password)->willReturn(false);

        $this->expectException(PasswordSpecialCharException::class);
        $sut->validate($password);
    }

    public function testValidationThrowsExceptionOnCorrectStrings(): void
    {
        $sut = $this->getSut(
            stringAnalysisService: $stringAnalysisMock = $this->createMock(StringAnalysisServiceInterface::class),
        );

        $password = uniqid();
        $stringAnalysisMock->method('hasSpecialCharacter')->with($password)->willReturn(true);

        $sut->validate($password);
        $this->addToAssertionCount(1);
    }

    /** @dataProvider boolDataProvider */
    public function testValidationWithDisabledSetting(bool $settingValue, bool $expectedValue): void
    {
        $sut = $this->getSut(
            moduleSetting: $this->createConfiguredStub(ModuleSettingInterface::class, [
                'getPasswordSpecialChar' => $settingValue
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
        ModuleSettingInterface $moduleSetting = null,
        StringAnalysisServiceInterface $stringAnalysisService = null,
    ): SpecialCharValidator {
        return new SpecialCharValidator(
            moduleSetting: $moduleSetting ?? $this->createStub(ModuleSettingInterface::class),
            stringAnalysisService: $stringAnalysisService ?? $this->createStub(StringAnalysisServiceInterface::class),
        );
    }
}
