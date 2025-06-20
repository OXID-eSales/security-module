<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

class StringAnalysisService implements StringAnalysisServiceInterface
{
    public function __construct(
        private readonly CharacterAnalysisServiceInterface $charAnalysisService
    ) {
    }

    public function hasUpperCaseCharacter(string $origin): bool
    {
        $passwordChars = $this->getPasswordChars($origin);

        foreach ($passwordChars as $charCode) {
            if ($this->charAnalysisService->isUpperCase($charCode)) {
                return true;
            }
        }

        return false;
    }

    public function hasLowerCaseCharacter(string $origin): bool
    {
        $passwordChars = $this->getPasswordChars($origin);

        foreach ($passwordChars as $charCode) {
            if ($this->charAnalysisService->isLowerCase($charCode)) {
                return true;
            }
        }

        return false;
    }

    public function hasSpecialCharacter(string $origin): bool
    {
        $passwordChars = $this->getPasswordChars($origin);

        foreach ($passwordChars as $charCode) {
            if (
                $this->charAnalysisService->isSpecialChar($charCode) or
                $this->charAnalysisService->isOtherChar($charCode)
            ) {
                return true;
            }
        }

        return false;
    }

    public function hasDigitCharacter(string $origin): bool
    {
        $passwordChars = $this->getPasswordChars($origin);

        foreach ($passwordChars as $charCode) {
            if ($this->charAnalysisService->isDigit($charCode)) {
                return true;
            }
        }

        return false;
    }

    private function getPasswordChars(string $origin): array
    {
        /** @var array $passwordChars */
        $passwordChars = count_chars($origin, 1);
        return array_keys($passwordChars);
    }
}
