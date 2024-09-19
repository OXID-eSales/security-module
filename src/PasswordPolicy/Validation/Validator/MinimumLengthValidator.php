<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordMinimumLengthException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;

class MinimumLengthValidator implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSetting
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleSetting->getPasswordMinimumLength() > 0;
    }

    public function validate(#[\SensitiveParameter] string $password): void
    {
        $minimumLength = $this->moduleSetting->getPasswordMinimumLength();
        if (strlen($password) < $minimumLength) {
            throw new PasswordMinimumLengthException($minimumLength);
        }
    }
}
