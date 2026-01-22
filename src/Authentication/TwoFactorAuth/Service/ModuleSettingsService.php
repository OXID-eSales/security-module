<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class ModuleSettingsService implements ModuleSettingsServiceInterface
{
    public const ACTIVE = 'oeSecurityTwoFactorAuthEnabled';

    public const TWO_FACTOR_TYPE = 'oeSecurityTwoFactorAuthType';

    public function __construct(
        readonly private ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function isTwoFactorAuthEnabled(): bool
    {
        return $this->moduleSettingService->getBoolean(self::ACTIVE, Module::MODULE_ID);
    }

    public function getTwoFactorAuthType(): string
    {
        return $this->getStringValue(self::TWO_FACTOR_TYPE);
    }

    public function saveIsTwoFactorAuthEnabled(bool $value): void
    {
        $this->moduleSettingService->saveBoolean(
            self::ACTIVE,
            $value,
            Module::MODULE_ID
        );
    }

    private function getStringValue(string $key): string
    {
        return $this->moduleSettingService->getString(
            $key,
            Module::MODULE_ID
        )
            ->trim()
            ->toString();
    }
}
