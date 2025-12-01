<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure;

use OxidEsales\Eshop\Application\Model\User;

interface UserFactoryInterface
{
    public function create(): User;
}
