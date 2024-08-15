<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Validator;

interface PasswordValidatorInterface
{
    public function validate(#[\SensitiveParameter] string $password): void;
}
