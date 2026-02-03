<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Validator;

use DateTimeInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;

interface OTPValidatorInterface
{
    /**
     * @throws InvalidCodeException
     */
    public function validateCode(?string $userCode, string $inputCode): void;

    public function checkLoginAttempts(int $attempts): void;

    public function checkExpirationTime(?DateTimeInterface $expiresAt): void;

    public function getMaxAttempts(): int;
}
