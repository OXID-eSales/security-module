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
            if ($this->hasControlChar($charCode)) {
                $control = 33;
            }
            if ($this->hasDigit($charCode)) {
                $digit = 10;
            }
            if ($this->hasUpperCase($charCode)) {
                $upper = 26;
            }
            if ($this->hasLowerCase($charCode)) {
                $lower = 26;
            }
            if ($this->hasSpecialChar($charCode)) {
                $other = 33;
            }
            if ($this->hasOtherChar($charCode)) {
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

    public function hasControlChar(int $charCode): bool
    {
        if ($charCode < 32 || 127 === $charCode) {
            return true;
        }

        return false;
    }

    public function hasDigit(int $charCode): bool
    {
        if (48 <= $charCode && $charCode <= 57) {
            return true;
        }

        return false;
    }

    public function hasUpperCase(int $charCode): bool
    {
        if (65 <= $charCode && $charCode <= 90) {
            return true;
        }

        return false;
    }

    public function hasLowerCase(int $charCode): bool
    {
        if (97 <= $charCode && $charCode <= 122) {
            return true;
        }

        return false;
    }

    public function hasSpecialChar(int $charCode): bool
    {
        if (
            ($charCode >= 32 && $charCode <= 47) or
            ($charCode >= 58 && $charCode <= 64) or
            ($charCode >= 91 && $charCode <= 96) or
            ($charCode >= 123 && $charCode <= 126)
        ) {
            return true;
        }

        return false;
    }

    public function hasOtherChar(int $charCode): bool
    {
        if ($charCode >= 128) {
            return true;
        }

        return false;
    }
}
