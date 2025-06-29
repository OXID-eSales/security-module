<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;

interface PasswordValidatorInterface
{
    public function isEnabled(): bool;

    /**
     * @throws PasswordValidateException
     */
    public function validate(#[\SensitiveParameter] string $password): void;
}
