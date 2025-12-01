<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Repository;

use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;

interface UserRepositoryInterface
{
    public function updateAttempts(string $userId, int $attempts): int;

    public function resetCodeFields(string $userId): void;

    public function addOTPtoUser(string $userId, string $otp, int $expiresAt): bool;
}
