<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

class OtpCodeGeneratorService implements OtpCodeGeneratorServiceInterface
{
    public function generateCode(): string
    {
        // todo-low: length from settings
        $length = 6;

        return str_pad((string) random_int(0, 999999), $length, '0', STR_PAD_LEFT);
    }
}
