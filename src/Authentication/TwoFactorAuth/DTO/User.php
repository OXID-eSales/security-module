<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO;

use DateTimeImmutable;

class User implements UserInterface
{
    public function __construct(
        private readonly string $userId,
        private readonly ?string $code,
        private readonly ?int $attempts,
        private readonly ?int $expiresAt,
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

    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        //todo: possible bug - should be null if not set
        $expireAt = $this->expiresAt ?? time();

        $dateTime = new DateTimeImmutable();

        return $dateTime->setTimestamp($expireAt);
    }
}
