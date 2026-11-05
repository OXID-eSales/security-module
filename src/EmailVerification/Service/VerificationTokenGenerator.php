<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

class VerificationTokenGenerator implements VerificationTokenGeneratorInterface
{
    private const TOKEN_BYTES = 32;

    public function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_BYTES));
    }
}
