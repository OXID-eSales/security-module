<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidatorInterface;

readonly class OTPService implements OTPServiceInterface
{
    public function __construct(
        private OTPValidatorInterface $otpValidator,
    ) {
    }

    public function validateCode(string $code): void
    {
        $this->otpValidator->checkLoginAttempts();
        $this->otpValidator->checkExpirationTime();
        $this->otpValidator->validateCode($code);
    }
}
