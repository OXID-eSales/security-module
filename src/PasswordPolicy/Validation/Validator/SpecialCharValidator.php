<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;

class SpecialCharValidator implements PasswordValidatorInterface
{
    public function __construct(
        private ModuleSettingInterface $moduleSetting
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
        foreach ($passwordChars as $char) {
            if (
                ($char >= 32 && $char <= 47) or
                ($char >= 58 && $char <= 64) or
                ($char >= 91 && $char <= 96) or
                ($char >= 123 && $char <= 126) or
                $char >= 128
            ) {
                $symbol++;
            }
        }

        if ($symbol == 0) {
            throw new PasswordValidate();
        }
    }
}
