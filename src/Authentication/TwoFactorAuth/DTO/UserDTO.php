<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO;

class UserDTO implements UserDTOInterface
{
    public function __construct(
        private readonly ?string $code,
        private readonly ?int $attempts,
        private readonly ?int $expiresAt,
    ) {
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getAttempts(): ?int
    {
        return $this->attempts;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }
}
