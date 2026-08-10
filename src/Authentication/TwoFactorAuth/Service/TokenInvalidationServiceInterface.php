<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface TokenInvalidationServiceInterface
{
    public function invalidateForUser(string $userId): void;
}
