<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception;

class MalformedRequestException extends TwoFAException
{
    public function __construct()
    {
        parent::__construct('ERROR_MALFORMED_REQUEST');
    }
}
