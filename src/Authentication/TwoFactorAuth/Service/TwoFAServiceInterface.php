<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\CodeValidationException;

interface TwoFAServiceInterface
{
    public function isVerified(string $userId): bool;

    public function triggerChallenge(string $userId): void;

    public function invalidateChallenge(string $userId): void;

    public function consumeChallenge(string $userId): void;

    /**
     * @throws CodeValidationException
     */
    public function verify(string $userId, #[\SensitiveParameter] string $code): void;
}
