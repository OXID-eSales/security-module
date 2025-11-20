<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserDTOInterface;

interface OTPValidatorInterface
{
    public function validateCode(string $userCode, string $inputCode): void;

    public function checkLoginAttempts(int $attempts): void;

    public function checkExpirationTime(int $expiresAt): void;
}
