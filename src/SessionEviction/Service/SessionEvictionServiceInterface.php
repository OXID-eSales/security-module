<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Service;

use OxidEsales\Eshop\Application\Model\User;

interface SessionEvictionServiceInterface
{
    public function evictUnverifiableSession(User $user, bool $forceAdmin): void;

    public function bindLoginTokenToSession(User $user, bool $userLoaded): void;

    public function readCurrentPasswordHash(User $user): string;

    public function refreshTokenAfterPasswordChange(User $user, string $passwordHashBeforeSave): void;
}
