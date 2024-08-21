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
    #[DataProvider('dataProviderPasswordStrength')]
    public function testPasswordStrengthEstimation(string $password, int $expectedStrength): void
    {
        $passwordStrengthService = new PasswordStrength();
        $passwordStrength = $passwordStrengthService->estimateStrength($password);

        $this->assertSame($expectedStrength, $passwordStrength);
    }


    public static function dataProviderPasswordStrength(): iterable
    {
        yield ['123456', PasswordStrength::STRENGTH_VERY_WEAK];
        yield ['weak-password', PasswordStrength::STRENGTH_WEAK];
        yield ['medium-passw0rd', PasswordStrength::STRENGTH_MEDIUM];
        yield ['g00d-enough-passw0rd', PasswordStrength::STRENGTH_STRONG];
        yield ['very-str00ng-Pa55word!', PasswordStrength::STRENGTH_VERY_STRONG];
        yield ['very-str00ng-Pa55word¿' . chr(127), PasswordStrength::STRENGTH_VERY_STRONG];
    }

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
