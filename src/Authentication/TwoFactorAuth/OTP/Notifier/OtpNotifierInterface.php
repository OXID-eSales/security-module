<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;

interface OtpNotifierInterface
{
    /**
     * @throws UserNotFoundException
     */
    public function notify(string $userId, #[\SensitiveParameter] string $code): void;
}
