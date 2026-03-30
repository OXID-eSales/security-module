<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;

interface OtpChallengeStateRepositoryInterface
{
    public function findByUserId(string $userId): ?OtpChallengeStateInterface;

    public function createChallengeState(string $userId, string $codeHash, DateTimeImmutable $expiresAt): void;

    public function markVerified(string $userId): void;

    public function markResent(string $userId, DateTimeImmutable $expiresAt): void;

    public function incrementAttempts(string $userId): void;

    public function deleteChallengeState(string $userId): void;
}
