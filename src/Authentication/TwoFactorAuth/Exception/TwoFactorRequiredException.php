<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception;

class TwoFactorRequiredException extends TwoFAException
{
    public function __construct(
        private readonly string $userId,
        private readonly string $verificationUrl,
    ) {
        parent::__construct('ERROR_TWO_FACTOR_REQUIRED');
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getVerificationUrl(): string
    {
        return $this->verificationUrl;
    }
}
