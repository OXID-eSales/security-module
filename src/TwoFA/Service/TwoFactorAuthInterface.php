<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\TwoFA\Service;

interface TwoFactorAuthInterface
{
    public function QRCodeGenerate(string $username): string;

    public function generateOTPCode(): int;
}
