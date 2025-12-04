<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\NotifierNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\NotifierAdapterInterface;

interface NotifierCollectorInterface
{
    /**
     * @throws NotifierNotFoundException
     */
    public function getNotifier(string $name): NotifierAdapterInterface;
}
