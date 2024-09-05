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
        private CharacterAnalysisInterface $characterAnalysisService
    ){
    }

    public function hasUpperCaseCharacter(string $origin): bool
    {
        /** @var array $passwordChars */
        $passwordChars = count_chars($origin, 1);
        $passwordChars = array_keys($passwordChars);

        foreach ($passwordChars as $charCode) {
            if ($this->characterAnalysisService->isUpperCase($charCode)) {
                return true;
            }
        }

        return false;
    }
}
