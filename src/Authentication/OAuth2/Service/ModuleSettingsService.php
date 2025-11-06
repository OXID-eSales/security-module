<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class ModuleSettingsService implements ModuleSettingsServiceInterface
{
    public const FACEBOOK_ACTIVE = 'oeSecurityFacebookEnable';
    public const FACEBOOK_CLIENT_ID = 'oeSecurityFacebookClientId';
    public const FACEBOOK_CLIENT_SECRET = 'oeSecurityFacebookSecret';
    public const FACEBOOK_REDIRECT_URL = 'oeSecurityFacebookRedirectUrl';

    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function isFacebookActive(): bool
    {
        return $this->moduleSettingService->getBoolean(self::FACEBOOK_ACTIVE, Module::MODULE_ID);
    }

    public function getFacebookClientId(): string
    {
        return $this->getStringValue(self::FACEBOOK_CLIENT_ID);
    }

    public function getFacebookClientSecret(): string
    {
        return $this->getStringValue(self::FACEBOOK_CLIENT_SECRET);
    }

    public function getFacebookRedirectUrl(): string
    {
        return $this->getStringValue(self::FACEBOOK_REDIRECT_URL);
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
