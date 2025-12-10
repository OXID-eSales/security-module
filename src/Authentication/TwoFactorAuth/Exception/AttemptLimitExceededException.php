<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception;

class AttemptLimitExceededException extends \Exception
{
    public function __construct()
    {
        parent::__construct('ERROR_ATTEMPT_LIMIT_EXCEEDED');
    }
}
