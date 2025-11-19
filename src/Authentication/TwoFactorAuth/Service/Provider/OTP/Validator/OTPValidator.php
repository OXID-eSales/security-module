<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;

readonly class OTPValidator implements OTPValidatorInterface
{
    public function __construct(
        private UserModel $userModel
    ) {
        //todo: use loaded user model
    }

    public function validateCode(string $code): void
    {
        if (strlen($code) < 1) {
            throw new InvalidCodeException();
        }


        if ($this->userModel->getFieldData('OTPCODE') !== $code) {
            throw new InvalidCodeException();
        }
    }

    public function checkLoginAttempts(): void
    {
        //todo: setting how much attempts user has?
        if ($this->userModel->getFieldData('OTPATTEMPTS') >= 5) {
            throw new AttemptLimitExceededException();
        }
    }

    public function checkExpirationTime(): void
    {
        //todo: setting how long password is valid?
        if ($this->userModel->getFieldData('OTPEXPIRETIME') >= 5) {
            throw new TimeExpiredException();
        }
    }
}
