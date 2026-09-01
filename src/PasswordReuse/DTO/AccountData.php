<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordReuse\DTO;

class AccountData implements AccountDataInterface
{
    public function __construct(
        private readonly string $userId,
        private readonly string $email,
        private readonly string $rights,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRights(): string
    {
        return $this->rights;
    }
}
