<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface ResendOTPServiceInterface
{
    public function markAsSent(string $userName): void;

    public function canSend(string $userName): bool;
}
