<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrength;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PasswordStrengthTest extends TestCase
{
    #[DataProvider('dataProviderPasswordContainsChar')]
    public function testCheckers($method, $value, $expected): void
    {
        $passwordStrength = new PasswordStrength();

        $passwordChar = ord($value);
        $this->assertEquals($expected, $passwordStrength->$method($passwordChar));
    }

    public static function dataProviderPasswordContainsChar(): array
    {
        return [
            //Control chars
            [
                'method'   => 'hasControlChar',
                'value'    => chr(127),
                'expected' => true,
            ],
            [
                'method'   => 'hasControlChar',
                'value'    => '0',
                'expected' => false,
            ],
            //Digit chars
            [
                'method'   => 'hasDigit',
                'value'    => '6',
                'expected' => true,
            ],
            [
                'method'   => 'hasDigit',
                'value'    => 'D',
                'expected' => false,
            ],
            //Upper case chars
            [
                'method'   => 'hasUpperCase',
                'value'    => 'U',
                'expected' => true,
            ],
            [
                'method'   => 'hasUpperCase',
                'value'    => 'u',
                'expected' => false,
            ],
            //Lower case chars
            [
                'method'   => 'hasLowerCase',
                'value'    => 'l',
                'expected' => true,
            ],
            [
                'method'   => 'hasLowerCase',
                'value'    => 'L',
                'expected' => false,
            ],
            //Special symbol chars
            [
                'method'   => 'hasSpecialChar',
                'value'    => '!',
                'expected' => true,
            ],
            [
                'method'   => 'hasSpecialChar',
                'value'    => '@',
                'expected' => true,
            ],
            [
                'method'   => 'hasSpecialChar',
                'value'    => '_',
                'expected' => true,
            ],
            [
                'method'   => 'hasSpecialChar',
                'value'    => '~',
                'expected' => true,
            ],
            [
                'method'   => 'hasSpecialChar',
                'value'    => 'S',
                'expected' => false,
            ],
            [
                'method'   => 'hasSpecialChar',
                'value'    => 's',
                'expected' => false,
            ],
            //Other chars
            [
                'method'   => 'hasOtherChar',
                'value'    => 'ö',
                'expected' => true,
            ],
            [
                'method'   => 'hasOtherChar',
                'value'    => '!',
                'expected' => false,
            ],
        ];
    }
}
