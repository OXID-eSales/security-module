<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordDigitException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\CharacterAnalysisInterface;

class DigitValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingInterface $moduleSetting,
        private readonly CharacterAnalysisInterface $characterAnalysis
    ) {
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (!$this->moduleSetting->getPasswordDigit()) {
            return;
        }

        /** @var array $passwordChars */
        $passwordChars = count_chars($password, 1);
        $passwordChars = array_keys($passwordChars);

        $digit = 0;
        foreach ($passwordChars as $charCode) {
            if ($this->characterAnalysis->isDigit($charCode)) {
                $digit++;
            }
        }

        if ($digit == 0) {
            throw new PasswordDigitException();
        }
    }
}
