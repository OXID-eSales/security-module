<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class TwoFAShopSettings implements TwoFAShopSettingsInterface
{
    public const ACTIVE = 'oeSecurityTwoFactorAuthEnabled';

    public const TWO_FACTOR_TYPE = 'oeSecurityTwoFactorAuthType';

    public function __construct(
        private Config $config,
        private ModuleSettingServiceInterface $moduleSettingService,
    ) {
    }

    public function isTwoFactorAuthEnabled(): bool
    {
        return $this->moduleSettingService->getBoolean(self::ACTIVE, Module::MODULE_ID);
    }

    public function getTwoFactorAuthType(): string
    {
        return $this->moduleSettingService->getString(self::TWO_FACTOR_TYPE, Module::MODULE_ID)
            ->trim()
            ->toString();
    }

    public function getVerificationUrl(): string
    {
        return $this->config->getShopHomeUrl() . 'cl=oesm_twofactorauth';
    }
}
