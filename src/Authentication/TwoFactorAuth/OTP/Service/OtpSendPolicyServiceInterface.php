<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

interface OtpSendPolicyServiceInterface
{
    public function canSend(string $userId): bool;
}
