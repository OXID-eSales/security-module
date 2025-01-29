<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class ModuleSettingsService implements ModuleSettingsServiceInterface
{
    /** @var array<string, string> */
    private array $lifetimeMap = [
        '5min' => '5M',
        '15min' => '15M',
        '30min' => '30M',
    ];

    public const CAPTCHA_ENABLE = 'oeSecurityCaptchaEnable';

    public const CAPTCHA_LIFETIME = 'oeSecurityCaptchaLifeTime';

    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function isCaptchaEnabled(): bool
    {
        return $this->moduleSettingService->getBoolean(self::CAPTCHA_ENABLE, Module::MODULE_ID);
    }

    public function saveIsCaptchaEnabled(bool $value): void
    {
        $this->moduleSettingService->saveBoolean(self::CAPTCHA_ENABLE, $value, Module::MODULE_ID);
    }

    public function getCaptchaLifeTime(): string
    {
        $key = $this->moduleSettingService
            ->getString(static::CAPTCHA_LIFETIME, Module::MODULE_ID)
            ->toString();

        return $this->lifetimeMap[$key] ?? $this->lifetimeMap['15min'];
    }
}
