<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface TwoFAUserServiceInterface
{
    public function startChallengeForUser(string $userId): void;

    public function getPendingUserId(): ?string;

    public function loginUser(string $userId): void;

    public function abandonChallenge(string $userId): void;

    public function isChallengeVerified(string $userId): bool;

    public function isTwoFARequired(string $userId): bool;
}
