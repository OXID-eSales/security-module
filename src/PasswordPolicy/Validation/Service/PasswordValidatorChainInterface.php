<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Service;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\InvalidValidatorTypeException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordCollectionException;

/**
 * @throws InvalidValidatorTypeException
 */
interface PasswordValidatorChainInterface
{
    /**
     * @throws PasswordCollectionException
     */
    public function validatePassword(#[\SensitiveParameter] string $password): void;
}
