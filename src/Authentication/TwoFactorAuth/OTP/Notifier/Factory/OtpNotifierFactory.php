<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Factory;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Exception\OtpNotifierNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\OtpNotifierInterface;

class OtpNotifierFactory implements OtpNotifierFactoryInterface
{
    public function __construct(
        private array $notifiers,
    ) {
    }

    public function create(string $userId): OtpNotifierInterface
    {
        $channel = $this->getUserChannel($userId);

        return $this->notifiers[$channel] ?? throw new OtpNotifierNotFoundException();
    }

    private function getUserChannel(string $userId): string
    {
        // todo-high-implement: select the notifier by user settings

        return 'email';
    }
}
