<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\PasswordStrengthInterface;

class SpecialCharValidator implements PasswordValidatorInterface
{
    public function __construct(
        private ModuleSettingInterface $moduleSetting,
        private PasswordStrengthInterface $passwordStrength
    ) {
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (!$this->moduleSetting->getPasswordSpecialChar()) {
            return;
        }

        /** @var array $passwordChars */
        $passwordChars = count_chars($password, 1);
        $passwordChars = array_keys($passwordChars);

        $symbol = 0;
        foreach ($passwordChars as $charCode) {
            if (
                $this->passwordStrength->hasSpecialChar($charCode) or
                $this->passwordStrength->hasOtherChar($charCode)
            ) {
                $symbol++;
            }
        }

        if ($symbol == 0) {
            throw new PasswordValidate();
        }
    }
}
