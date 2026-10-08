<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Exception;

class VerificationTokenExpiredException extends EmailVerificationException
{
    public function __construct()
    {
        parent::__construct('ERROR_EMAIL_VERIFICATION_LINK_EXPIRED');
    }
}
