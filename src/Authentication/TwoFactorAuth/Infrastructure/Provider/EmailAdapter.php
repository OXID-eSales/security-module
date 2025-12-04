<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\Factory\EmailFactoryInterface;

class EmailAdapter
{
    public function __construct(
        readonly private EmailFactoryInterface $emailFactory,
    ) {
    }

    public function getName()
    {
        return 'email';
    }

    public function notify($recipient, $code): void
    {
        $emailModel = $this->emailFactory->create();
        $emailModel->sendEmail(
            $recipient,
            'Your verification code',
            "Your verification code is: {$code}"
        );
    }
}
