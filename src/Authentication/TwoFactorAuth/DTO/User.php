<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO;

class User implements UserInterface
{
    public function __construct(
        private string $userId,
        private string $email,
        private bool $twoFAEnabled,
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

    public function isTwoFAEnabled(): bool
    {
        return $this->twoFAEnabled;
    }
}
