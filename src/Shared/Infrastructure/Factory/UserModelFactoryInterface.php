<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Shared\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User;

interface UserModelFactoryInterface
{
    public function create(): User;
}
