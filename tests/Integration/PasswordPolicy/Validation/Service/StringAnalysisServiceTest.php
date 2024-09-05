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
        yield 'one char string' => [
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

    public function getSut(): StringAnalysisServiceInterface
    {
        return new StringAnalysisService(
            characterAnalysisService: $this->get(CharacterAnalysisInterface::class)
        );
    }
}
