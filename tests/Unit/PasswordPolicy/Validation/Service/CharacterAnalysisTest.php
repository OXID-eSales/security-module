<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CharacterAnalysisTest extends TestCase
{
    #[DataProvider('dataProviderPasswordContainsChar')]
    public function testCheckers($method, $value, $expected): void
    {
        $characterAnalysis = new CharacterAnalysis();

        $passwordChar = ord($value);
        $this->assertEquals($expected, $characterAnalysis->$method($passwordChar));
    }

    public static function dataProviderPasswordContainsChar(): array
    {
        return [
            //Control chars
            [
                'method'   => 'isControlChar',
                'value'    => chr(127),
                'expected' => true,
            ],
            [
                'method'   => 'isControlChar',
                'value'    => '0',
                'expected' => false,
            ],
            //Digit chars
            [
                'method'   => 'isDigit',
                'value'    => '6',
                'expected' => true,
            ],
            [
                'method'   => 'isDigit',
                'value'    => 'D',
                'expected' => false,
            ],
            //Upper case chars
            [
                'method'   => 'isUpperCase',
                'value'    => 'U',
                'expected' => true,
            ],
            [
                'method'   => 'isUpperCase',
                'value'    => 'u',
                'expected' => false,
            ],
            //Lower case chars
            [
                'method'   => 'isLowerCase',
                'value'    => 'l',
                'expected' => true,
            ],
            [
                'method'   => 'isLowerCase',
                'value'    => 'L',
                'expected' => false,
            ],
            //Special symbol chars
            [
                'method'   => 'isSpecialChar',
                'value'    => '!',
                'expected' => true,
            ],
            [
                'method'   => 'isSpecialChar',
                'value'    => '@',
                'expected' => true,
            ],
            [
                'method'   => 'isSpecialChar',
                'value'    => '_',
                'expected' => true,
            ],
            [
                'method'   => 'isSpecialChar',
                'value'    => '~',
                'expected' => true,
            ],
            [
                'method'   => 'isSpecialChar',
                'value'    => 'S',
                'expected' => false,
            ],
            [
                'method'   => 'isSpecialChar',
                'value'    => 's',
                'expected' => false,
            ],
            //Other chars
            [
                'method'   => 'isOtherChar',
                'value'    => 'ö',
                'expected' => true,
            ],
            [
                'method'   => 'isOtherChar',
                'value'    => '!',
                'expected' => false,
            ],
        ];
    }
}
