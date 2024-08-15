<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;

class UpperCaseValidator implements PasswordValidatorInterface
{
    public function __construct(
        private ModuleSettingInterface $moduleSetting
    ) {
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (!$this->moduleSetting->getPasswordUppercase()) {
            return;
        }

        /** @var array $passwordChars */
        $passwordChars = count_chars($password, 1);
        $passwordChars = array_keys($passwordChars);

        $upper = 0;
        foreach ($passwordChars as $char) {
            if (65 <= $char && $char <= 90) {
                $upper++;
            }
        }

        if ($upper == 0) {
            throw new PasswordValidate();
        }
    }
}
