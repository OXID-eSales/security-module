<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\Eshop\Application\Model\User as UserModel;

interface OTPServiceInterface
{
    public function validateCode(UserModel $user, string $code): void;
}
