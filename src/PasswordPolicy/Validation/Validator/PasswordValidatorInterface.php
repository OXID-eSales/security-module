<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateExceptionInterface;

interface PasswordValidatorInterface
{
    public function isEnabled(): bool;

    /**
     * @throws PasswordValidateExceptionInterface
     */
    public function validate(#[\SensitiveParameter] string $password): void;
}
