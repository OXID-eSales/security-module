<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\ResendCooldownException;

interface TwoFAResendableInterface
{
    /** @throws ResendCooldownException */
    public function resend(string $userId): void;

    public function getRemainingAttempts(string $userId): int;
}
