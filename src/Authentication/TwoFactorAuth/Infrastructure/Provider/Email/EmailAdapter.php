<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\Email;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\NotifierAdapterInterface;

class EmailAdapter implements NotifierAdapterInterface
{
    public function __construct(
        readonly private EmailFactoryInterface $emailFactory,
    ) {
    }

    public function getName(): string
    {
        return 'email';
    }

    public function notify(string $recipient, #[\SensitiveParameter] string $code): void
    {
        //todo: replayto is same as recipient
        $emailModel = $this->emailFactory->create();
        $emailModel->sendEmail(
            $recipient,
            'Your verification code',
            "Your verification code is: {$code}"
        );
    }
}
