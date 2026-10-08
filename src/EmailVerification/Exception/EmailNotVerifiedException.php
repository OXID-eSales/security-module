<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Exception;

class EmailNotVerifiedException extends EmailVerificationException
{
    public function __construct(
        private readonly string $userId,
    ) {
        parent::__construct('ERROR_EMAIL_NOT_VERIFIED');
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
