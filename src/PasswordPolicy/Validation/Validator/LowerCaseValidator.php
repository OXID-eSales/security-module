<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordLowerCaseException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Service\CharacterAnalysisInterface;

class LowerCaseValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingInterface $moduleSetting,
        private readonly CharacterAnalysisInterface $passwordStrength
    ) {
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (!$this->moduleSetting->getPasswordLowercase()) {
            return;
        }

        /** @var array $passwordChars */
        $passwordChars = count_chars($password, 1);
        $passwordChars = array_keys($passwordChars);

        $upper = 0;
        foreach ($passwordChars as $charCode) {
            if ($this->passwordStrength->isLowerCase($charCode)) {
                $upper++;
            }
        }

        if ($upper == 0) {
            throw new PasswordLowerCaseException();
        }
    }
}
