<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

class PasswordStrength implements PasswordStrengthInterface
{
    public const STRENGTH_VERY_WEAK = 0;
    public const STRENGTH_WEAK = 1;
    public const STRENGTH_MEDIUM = 2;
    public const STRENGTH_STRONG = 3;
    public const STRENGTH_VERY_STRONG = 4;

    public function estimateStrength(string $password): int
    {
        $length = strlen($password);

        /** @var array $passwordChars */
        $passwordChars = count_chars($password, 1);
        $passwordChars = array_keys($passwordChars);
        $chars = \count($passwordChars);

        $control = $digit = $upper = $lower = $symbol = $other = 0;

        foreach ($passwordChars as $charCode) {
            if ($this->isControlChar($charCode)) {
                $control = 33;
            }
            if ($this->isDigit($charCode)) {
                $digit = 10;
            }
            if ($this->isUpperCase($charCode)) {
                $upper = 26;
            }
            if ($this->isLowerCase($charCode)) {
                $lower = 26;
            }
            if ($this->isSpecialChar($charCode)) {
                $other = 33;
            }
            if ($this->isOtherChar($charCode)) {
                $symbol = 128;
            }
        }

        $pool = $lower + $upper + $digit + $symbol + $control + $other;
        $entropy =  $chars * log($pool, 2) + ($length - $chars) * log($chars, 2);

        return match (true) {
            $entropy >= 120 => PasswordStrength::STRENGTH_VERY_STRONG,
            $entropy >= 100 => PasswordStrength::STRENGTH_STRONG,
            $entropy >= 80 => PasswordStrength::STRENGTH_MEDIUM,
            $entropy >= 60 => PasswordStrength::STRENGTH_WEAK,
            default => PasswordStrength::STRENGTH_VERY_WEAK,
        };
    }

    public function isControlChar(int $charCode): bool
    {
        return $charCode < 32 || 127 === $charCode;
    }

    public function isDigit(int $charCode): bool
    {
        return 48 <= $charCode && $charCode <= 57;
    }

    public function isUpperCase(int $charCode): bool
    {
        return 65 <= $charCode && $charCode <= 90;
    }

    public function isLowerCase(int $charCode): bool
    {
        return 97 <= $charCode && $charCode <= 122;
    }

    public function isSpecialChar(int $charCode): bool
    {
        return (
            ($charCode >= 32 && $charCode <= 47) or
            ($charCode >= 58 && $charCode <= 64) or
            ($charCode >= 91 && $charCode <= 96) or
            ($charCode >= 123 && $charCode <= 126)
        );
    }

    public function isOtherChar(int $charCode): bool
    {
        return $charCode >= 128;
    }
}
