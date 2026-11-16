<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Service;

interface SessionEvictionGuardInterface
{
    public function hasLoginToken(): bool;

    public function getUserId(string $userIdParameter): string;

    public function destroySession(): void;
}
