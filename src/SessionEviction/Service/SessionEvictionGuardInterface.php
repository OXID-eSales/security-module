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

    public function hasUserId(string $userIdParameter): bool;

    public function forgetUserId(string $userIdParameter): void;

    public function isActingSessionUser(string $userId, string $userIdParameter): bool;
}
