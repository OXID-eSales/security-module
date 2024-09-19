<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

class PasswordStrengthService implements PasswordStrengthServiceInterface
{
    public const STRENGTH_VERY_WEAK = 0;
    public const STRENGTH_WEAK = 1;
    public const STRENGTH_MEDIUM = 2;
    public const STRENGTH_STRONG = 3;
    public const STRENGTH_VERY_STRONG = 4;

    public function __construct(
        private readonly CharacterAnalysisServiceInterface $characterAnalysis
    ) {
    }

    public function estimateStrength(string $password): int
    {
        $length = strlen($password);

        /** @var array $passwordChars */
        $passwordChars = count_chars($password, 1);
        $passwordChars = array_keys($passwordChars);
        $chars = \count($passwordChars);

        $control = $digit = $upper = $lower = $symbol = $other = 0;

        foreach ($passwordChars as $charCode) {
            if ($this->characterAnalysis->isControlChar($charCode)) {
                $control = 33;
            }
            if ($this->characterAnalysis->isDigit($charCode)) {
                $digit = 10;
            }
            if ($this->characterAnalysis->isUpperCase($charCode)) {
                $upper = 26;
            }
            if ($this->characterAnalysis->isLowerCase($charCode)) {
                $lower = 26;
            }
            if ($this->characterAnalysis->isSpecialChar($charCode)) {
                $other = 33;
            }
            if ($this->characterAnalysis->isOtherChar($charCode)) {
                $symbol = 128;
            }
        }

        $pool = $lower + $upper + $digit + $symbol + $control + $other;
        $entropy =  $chars * log($pool, 2) + ($length - $chars) * log($chars, 2);

        return match (true) {
            $entropy >= 120 => self::STRENGTH_VERY_STRONG,
            $entropy >= 100 => self::STRENGTH_STRONG,
            $entropy >= 80 => self::STRENGTH_MEDIUM,
            $entropy >= 60 => self::STRENGTH_WEAK,
            default => self::STRENGTH_VERY_WEAK,
        };
    }
}
