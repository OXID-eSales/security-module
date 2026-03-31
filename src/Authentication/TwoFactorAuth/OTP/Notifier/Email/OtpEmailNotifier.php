<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Email;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\OtpNotifierInterface;

class OtpEmailNotifier implements OtpNotifierInterface
{
    public function notify(string $userId, #[\SensitiveParameter] string $code): void
    {
        // todo-critical: implement email notifier
    }
}
