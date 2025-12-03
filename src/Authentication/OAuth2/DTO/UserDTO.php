<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\DTO;

class UserDTO implements UserDTOInterface
{
    public function __construct(
        private readonly string $userId,
        private readonly bool $isBlocked
    ) {
    }

    public function getId(): string
    {
        return $this->userId;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }
}
