<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\PasswordPolicy\Validation\Service;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\CharacterAnalysisInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\StringAnalysisService;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\StringAnalysisServiceInterface;

class StringAnalysisServiceTest extends IntegrationTestCase
{
    /** @dataProvider dataProviderUpperCase */
    public function testHasUpperCaseCharacterReturnsCorrectValue(string $origin, bool $expectedValue): void
    {
        $sut = $this->getSut();

        $this->assertSame($expectedValue, $sut->hasUpperCaseCharacter($origin));
    }

    public static function dataProviderUpperCase(): \Generator
    {
        yield 'one correct char string' => [
            'origin' => 'A',
            'expectedValue' => true,
        ];

        yield 'one char string wrong' => [
            'origin' => 'a',
            'expectedValue' => false,
        ];

        yield 'multi char string with correct char' => [
            'origin' => 'AAAA',
            'expectedValue' => true,
        ];

        yield 'multi char string with one correct char' => [
            'origin' => 'sdAf',
            'expectedValue' => true,
        ];
    }

    /** @dataProvider dataProviderDigitCase */
    public function testHasDigitReturnsCorrectValue(string $origin, bool $expectedValue): void
    {
        $sut = $this->getSut();

        $this->assertSame($expectedValue, $sut->hasDigitCharacter($origin));
    }

    public static function dataProviderDigitCase(): \Generator
    {
        yield 'one digit string' => [
            'origin' => '1',
            'expectedValue' => true,
        ];

        yield 'no digit string' => [
            'origin' => 'A',
            'expectedValue' => false,
        ];

        yield 'multi char string with one digit' => [
            'origin' => 'sd6f',
            'expectedValue' => true,
        ];

        yield 'multi char string without digit' => [
            'origin' => 'sdAf',
            'expectedValue' => false,
        ];
    }


    /** @dataProvider dataProviderLowerCase */
    public function testHasLowerCaseCharacterReturnsCorrectValue(string $origin, bool $expectedValue): void
    {
        $sut = $this->getSut();

        $this->assertSame($expectedValue, $sut->hasLowerCaseCharacter($origin));
    }

    public static function dataProviderLowerCase(): \Generator
    {
        yield 'one correct char string' => [
            'origin' => 'a',
            'expectedValue' => true,
        ];

        yield 'one char string wrong' => [
            'origin' => 'A',
            'expectedValue' => false,
        ];

        yield 'multi char string with correct char' => [
            'origin' => 'aaaa',
            'expectedValue' => true,
        ];

        yield 'multi char string with one correct char' => [
            'origin' => 'sdAf',
            'expectedValue' => true,
        ];
    }

    /** @dataProvider dataProviderSpecialChar */
    public function testHasSpecialCharacterReturnsCorrectValue(string $origin, bool $expectedValue): void
    {
        $sut = $this->getSut();

        $this->assertSame($expectedValue, $sut->hasSpecialCharacter($origin));
    }

    public static function dataProviderSpecialChar(): \Generator
    {
        yield 'one correct special char string' => [
            'origin' => '!',
            'expectedValue' => true,
        ];

        yield 'missing special char string' => [
            'origin' => 'A',
            'expectedValue' => false,
        ];

        yield 'multi char string with correct special char' => [
            'origin' => 'aa@aa',
            'expectedValue' => true,
        ];

        yield 'multi char string with one correct special char' => [
            'origin' => 'sd@Af',
            'expectedValue' => true,
        ];

        yield 'multi char string without correct special char' => [
            'origin' => 'sdAf',
            'expectedValue' => False,
        ];
    }


    public function getSut(): StringAnalysisServiceInterface
    {
        return new StringAnalysisService(
            characterAnalysisService: $this->get(CharacterAnalysisInterface::class)
        );
    }
}
