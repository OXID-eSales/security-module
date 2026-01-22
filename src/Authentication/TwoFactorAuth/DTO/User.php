<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO;

use DateTimeInterface;

class User implements UserInterface
{
    public function __construct(
        private readonly string $userId,
        private readonly int $attempts,
        private readonly ?string $code,
        private readonly ?DateTimeInterface $expiresAt,
    ) {
    }

    public function getId(): string
    {
        return $this->userId;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }
}
