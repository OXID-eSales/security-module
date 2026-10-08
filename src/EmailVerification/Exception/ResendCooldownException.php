<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Exception;

class ResendCooldownException extends EmailVerificationException
{
    public function __construct(
        private readonly int $remainingSeconds,
    ) {
        parent::__construct('ERROR_EMAIL_VERIFICATION_RESEND_COOLDOWN');
    }

    public function getRemainingSeconds(): int
    {
        return $this->remainingSeconds;
    }
}
