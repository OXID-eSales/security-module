<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidate;

class MinimumLengthValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingInterface $moduleSetting
    ) {
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        $minimumLength = $this->moduleSetting->getPasswordMinimumLength();
        if ($minimumLength <= 0) {
            return;
        }

        if (strlen($password) < $minimumLength) {
            throw new PasswordValidate();
        }
    }
}
