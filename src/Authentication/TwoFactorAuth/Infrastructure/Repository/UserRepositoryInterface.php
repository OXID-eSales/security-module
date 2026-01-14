<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use DateTime;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;

interface UserRepositoryInterface
{
    public function getUserOTPData(string $userName): UserInterface;

    public function updateAttempts(string $userId, int $attempts): void;

    public function resetCodeFields(string $userId): void;

    public function addOTPtoUser(string $userId, string $otp, DateTime $expiresAt): bool;

    public function getUserPasswordHash(string $userId): ?string;

    public function markOtpAsSent(string $userId): void;
}
