<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface UserServiceInterface
{
    public function handleLogin(string $userName): void;

    public function checkPassword(string $userName, string $password): bool;

    public function finalizeLogin(): void;
}
