<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Factory;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;

interface TwoFAServiceFactoryInterface
{
    public function create(): TwoFAServiceInterface;
}
