<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Exception;

class EmailAlreadyVerifiedException extends EmailVerificationException
{
    public function __construct()
    {
        parent::__construct('ERROR_EMAIL_ALREADY_VERIFIED');
    }
}
