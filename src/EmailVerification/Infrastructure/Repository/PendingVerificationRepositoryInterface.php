<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;

interface PendingVerificationRepositoryInterface
{
    public function findByUserId(string $userId): ?PendingVerificationDtoInterface;

    public function findByToken(#[\SensitiveParameter] string $token): ?PendingVerificationDtoInterface;

    public function createPendingVerification(
        string $userId,
        #[\SensitiveParameter] string $token,
        DateTimeImmutable $expiresAt
    ): void;

    public function refreshPendingVerification(
        string $userId,
        #[\SensitiveParameter] string $token,
        DateTimeImmutable $expiresAt
    ): void;

    public function deletePendingVerification(string $userId): void;
}
