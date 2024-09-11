<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception;

class PasswordSpecialCharException extends PasswordValidateException
{
    public function __construct()
    {
        parent::__construct('ERROR_PASSWORD_MISSING_SPECIAL_CHARACTER');
    }
}
