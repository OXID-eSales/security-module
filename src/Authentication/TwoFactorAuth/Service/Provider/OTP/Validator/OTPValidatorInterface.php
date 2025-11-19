<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator;

interface OTPValidatorInterface
{
    public function validateCode(string $code): void;

    public function checkLoginAttempts(): void;

    public function checkExpirationTime(): void;
}
