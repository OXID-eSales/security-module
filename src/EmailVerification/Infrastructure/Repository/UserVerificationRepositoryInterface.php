<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository;

interface UserVerificationRepositoryInterface
{
    public function markEmailVerified(string $userId): void;
}
