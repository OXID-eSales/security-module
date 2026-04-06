<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\CodeValidationException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\ResendCooldownException;

interface TwoFAServiceInterface
{
    public function isVerified(string $userId): bool;

    public function triggerChallenge(string $userId): void;

    public function invalidateChallenge(string $userId): void;

    /**
     * @throws CodeValidationException
     */
    public function verify(string $userId, #[\SensitiveParameter] string $code): void;

    // todo-low: consider extracting resend to a separate ResendableInterface, not all 2FA methods support it (e.g. TOTP)
    /** @throws ResendCooldownException */
    public function resend(string $userId): void;
}
