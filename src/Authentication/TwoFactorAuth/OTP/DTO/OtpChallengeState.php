<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO;

use DateTimeImmutable;

class OtpChallengeState implements OtpChallengeStateInterface
{
    public function __construct(
        private string $userId,
        private string $codeHash,
        private int $attempts,
        private ?DateTimeImmutable $lastSentAt,
        private DateTimeImmutable $expiresAt,
        private ?DateTimeImmutable $verifiedAt,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getCodeHash(): string
    {
        return $this->codeHash;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getLastSentAt(): ?DateTimeImmutable
    {
        return $this->lastSentAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getVerifiedAt(): ?DateTimeImmutable
    {
        return $this->verifiedAt;
    }
}
