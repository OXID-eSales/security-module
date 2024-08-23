<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception;

class PasswordUpperCaseException extends \Exception implements PasswordValidateExceptionInterface
{
    public function __construct()
    {
        parent::__construct('ERROR_PASSWORD_MISSING_UPPER_CASE');
    }
}
