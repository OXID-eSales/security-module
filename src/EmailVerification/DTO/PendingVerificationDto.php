<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\DTO;

use DateTimeImmutable;

class PendingVerificationDto implements PendingVerificationDtoInterface
{
    public function __construct(
        private string $userId,
        private DateTimeImmutable $expiresAt,
        private int $sendCount,
        private DateTimeImmutable $lastSentAt,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getSendCount(): int
    {
        return $this->sendCount;
    }

    public function getLastSentAt(): DateTimeImmutable
    {
        return $this->lastSentAt;
    }
}
