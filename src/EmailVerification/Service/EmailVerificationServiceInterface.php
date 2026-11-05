<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use OxidEsales\SecurityModule\EmailVerification\Exception\EmailAlreadyVerifiedException;
use OxidEsales\SecurityModule\EmailVerification\Exception\InvalidVerificationTokenException;
use OxidEsales\SecurityModule\EmailVerification\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationTokenExpiredException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;

interface EmailVerificationServiceInterface
{
    /**
     * @throws VerificationMailNotSentException
     */
    public function startEmailVerification(string $userId): void;

    /**
     * @throws InvalidVerificationTokenException
     * @throws VerificationTokenExpiredException
     */
    public function verifyEmail(#[\SensitiveParameter] string $token): void;

    /**
     * @throws InvalidVerificationTokenException
     * @throws ResendCooldownException
     * @throws VerificationMailNotSentException
     */
    public function resendVerificationEmailByToken(#[\SensitiveParameter] string $token): void;

    /**
     * @throws ResendCooldownException
     * @throws EmailAlreadyVerifiedException
     * @throws VerificationMailNotSentException
     */
    public function resendVerificationEmailForUser(string $userId): void;
}
