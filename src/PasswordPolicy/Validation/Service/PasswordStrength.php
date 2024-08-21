<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

class PasswordStrength implements PasswordStrengthInterface
{
    public function estimateStrength(string $password): float
    {
        $length = strlen($password);
        $password = count_chars($password, 1);
        $chars = \count($password);

        $control = $digit = $upper = $lower = $symbol = $other = 0;

        foreach ($password as $chr => $count) {
            if ($this->hasControlChar($chr)) {
                $control = 33;
            }
            if ($this->hasDigit($chr)) {
                $digit = 10;
            }
            if ($this->hasUpperCase($chr)) {
                $upper = 26;
            }
            if ($this->hasLowerCase($chr)) {
                $lower = 26;
            }
            if ($this->hasSpecialChar($chr)) {
                $other = 33;
            }
            if ($this->hasOtherChar($chr)) {
                $symbol = 128;
            }
        }

        $pool = $lower + $upper + $digit + $symbol + $control + $other;

        return $chars * log($pool, 2) + ($length - $chars) * log($chars, 2);
    }

    public function hasControlChar(int $char): bool
    {
        if ($char < 32 || 127 === $char) {
            return true;
        }

        return false;
    }

    public function hasDigit(int $char): bool
    {
        if (48 <= $char && $char <= 57) {
            return true;
        }

        return false;
    }

    public function hasUpperCase(int $char): bool
    {
        if (65 <= $char && $char <= 90) {
            return true;
        }

        return false;
    }

    public function hasLowerCase(int $char): bool
    {
        if (97 <= $char && $char <= 122) {
            return true;
        }

        return false;
    }

    public function hasSpecialChar(int $char): bool
    {
        if (
            ($char >= 32 && $char <= 47) or
            ($char >= 58 && $char <= 64) or
            ($char >= 91 && $char <= 96) or
            ($char >= 123 && $char <= 126)
        ) {
            return true;
        }

        return false;
    }

    public function hasOtherChar(int $char): bool
    {
        if ($char >= 128) {
            return true;
        }

        return false;
    }
}
