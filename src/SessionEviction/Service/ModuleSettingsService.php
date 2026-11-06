<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class ModuleSettingsService implements ModuleSettingsServiceInterface
{
    public const SESSION_EVICTION_ENABLE = 'oeSecuritySessionEvictionEnable';
    public const SESSION_EVICTION_STRICT = 'oeSecuritySessionEvictionStrict';

    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->moduleSettingService->getBoolean(self::SESSION_EVICTION_ENABLE, Module::MODULE_ID);
    }

    public function isStrict(): bool
    {
        return $this->moduleSettingService->getBoolean(self::SESSION_EVICTION_STRICT, Module::MODULE_ID);
    }
}
