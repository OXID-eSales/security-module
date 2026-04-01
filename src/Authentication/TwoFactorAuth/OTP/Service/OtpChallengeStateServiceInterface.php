<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;

interface OtpChallengeStateServiceInterface
{
    public function getChallengeState(string $userId): ?OtpChallengeStateInterface;

    public function createChallengeState(string $userId, #[\SensitiveParameter] string $code): void;

    public function refreshChallengeState(string $userId, #[\SensitiveParameter] string $code): void;

    public function markVerified(string $userId): void;

    public function incrementAttempts(string $userId): void;

    public function deleteChallengeState(string $userId): void;
}
