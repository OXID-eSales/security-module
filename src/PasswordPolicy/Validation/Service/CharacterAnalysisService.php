<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

class CharacterAnalysisService implements CharacterAnalysisServiceInterface
{
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
