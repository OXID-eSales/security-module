<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Validator;

use DateTimeInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;

readonly class OTPValidator implements OTPValidatorInterface
{
    private const MAX_ATTEMPTS = 5;

    public function validateCode(?string $userCode, string $inputCode): void
    {
        if (!$userCode || empty($inputCode)) {
            throw new InvalidCodeException();
        }

        if ($userCode !== $inputCode) {
            throw new InvalidCodeException();
        }
    }

    public function checkLoginAttempts(int $attempts): void
    {
        if ($attempts >= self::MAX_ATTEMPTS) {
            throw new AttemptLimitExceededException();
        }
    }

    public function checkExpirationTime(?DateTimeInterface $expiresAt): void
    {
        if (
            !($expiresAt instanceof DateTimeInterface)
            || time() > $expiresAt->getTimestamp()
        ) {
            throw new TimeExpiredException();
        }
    }
}
