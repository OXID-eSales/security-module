<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;

class OTPService implements TwoFAServiceInterface
{
    public function hasPendingChallenge(string $userId): bool
    {
    }

    public function triggerChallenge(string $userId): void
    {
    }

    public function invalidateChallenge(string $userId): void
    {
    }

    public function verify(string $userId, #[\SensitiveParameter] string $code): void
    {
    }

    public function resend(string $userId): void
    {
    }
}
