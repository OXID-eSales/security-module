<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface TwoFAServiceInterface
{
    public function isVerified(string $userId): bool;

    public function triggerChallenge(string $userId): void;

    public function invalidateChallenge(string $userId): void;

    public function verify(string $userId, #[\SensitiveParameter] string $code): void;

    public function resend(string $userId): void;
}
