<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface UserServiceInterface
{
    public function handleLogin($userName): void;

    public function checkPassword($password, $userId): bool;
}
