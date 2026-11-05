<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;

interface VerificationMailSenderInterface
{
    /**
     * @throws VerificationMailNotSentException
     */
    public function sendEmailVerificationMail(
        string $userId,
        #[\SensitiveParameter] string $token,
        DateTimeImmutable $expiresAt
    ): void;
}
