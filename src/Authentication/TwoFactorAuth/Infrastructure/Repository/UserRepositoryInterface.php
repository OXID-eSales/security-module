<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User as UserDTO;

interface UserRepositoryInterface
{
    public function getUserOTPData(string $userId): UserDTO;

    public function updateAttempts(string $userId, int $attempts): void;

    public function resetCodeFields(string $userId): void;

    public function addOTPtoUser(string $userId, string $otp, int $expiresAt): bool;

    public function getUserPasswordHash(string $userId): string;
}
