<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface TwoFactorAuthInterface
{
    public function generateQRCode(string $username): string;

    public function generateOTPCode(): int;
}
