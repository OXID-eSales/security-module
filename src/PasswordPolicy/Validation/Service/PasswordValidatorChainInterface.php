<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\InvalidValidatorTypeException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;

/**
 * @throws InvalidValidatorTypeException
 */
interface PasswordValidatorChainInterface
{
    /**
     * @throws PasswordValidateException
     */
    public function validatePassword(#[\SensitiveParameter] string $password): void;
}
