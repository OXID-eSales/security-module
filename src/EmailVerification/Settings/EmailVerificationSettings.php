<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Settings;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class EmailVerificationSettings implements EmailVerificationSettingsInterface
{
    public const ACTIVE = 'oeSecurityEmailVerificationEnabled';

    public const LINK_LIFETIME = 'oeSecurityEmailVerificationLinkLifetime';

    public const MIN_LINK_LIFETIME_HOURS = 1;

    public function __construct(
        private ModuleSettingServiceInterface $moduleSettingService,
    ) {
    }

    public function isEmailVerificationEnabled(): bool
    {
        return $this->moduleSettingService->getBoolean(self::ACTIVE, Module::MODULE_ID);
    }

    public function getLinkLifetimeHours(): int
    {
        return max(
            self::MIN_LINK_LIFETIME_HOURS,
            $this->moduleSettingService->getInteger(self::LINK_LIFETIME, Module::MODULE_ID)
        );
    }
}
