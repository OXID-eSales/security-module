<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface TwoFAServiceInterface
{
    public function hasPendingChallenge(string $userId): bool;

    public function triggerChallenge(string $userId): void;

    public function clearChallenge(): void;
}
