<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider;

interface NotifierAdapterInterface
{
    public function getName(): string;

    public function notify(string $recipient, string $code): void;
}
