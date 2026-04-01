<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings;

use OxidEsales\Eshop\Core\Config;

class TwoFASettings implements TwoFASettingsInterface
{
    public function __construct(
        private Config $config,
    ) {
    }

    public function getVerificationUrl(): string
    {
        return $this->config->getShopHomeUrl() . 'cl=twofactorauth';
    }
}
