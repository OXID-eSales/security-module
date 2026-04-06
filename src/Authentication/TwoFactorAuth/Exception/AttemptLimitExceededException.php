<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception;

class AttemptLimitExceededException extends CodeValidationException
{
    public function __construct()
    {
        parent::__construct('ERROR_ATTEMPT_LIMIT_EXCEEDED');
    }
}
