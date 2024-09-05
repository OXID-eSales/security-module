<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordMinimumLengthException;

class MinimumLengthValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingInterface $moduleSetting
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleSetting->getPasswordMinimumLength() > 0;
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        if (strlen($password) < $this->moduleSetting->getPasswordMinimumLength()) {
            throw new PasswordMinimumLengthException();
        }
    }
}
