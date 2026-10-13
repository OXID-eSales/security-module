<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Transput;

interface VerificationRequestInterface
{
    public function getToken(): string;

    public function isSentStateRequested(): bool;
}
