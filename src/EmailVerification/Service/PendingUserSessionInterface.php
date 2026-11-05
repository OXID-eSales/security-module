<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

interface PendingUserSessionInterface
{
    public function setPendingUserId(string $userId): void;

    public function getPendingUserId(): ?string;

    public function clearPendingUserId(): void;
}
