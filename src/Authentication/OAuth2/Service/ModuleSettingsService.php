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
    public const FACEBOOK_LOGIN_ENABLED = 'oeSecurityFacebookEnabled';
    public const FACEBOOK_CLIENT_ID = 'oeSecurityFacebookClientId';
    public const FACEBOOK_CLIENT_SECRET = 'oeSecurityFacebookSecret';
    public const FACEBOOK_REDIRECT_URL = 'oeSecurityFacebookRedirectUrl';
    public const GOOGLE_ACTIVE = 'oeSecurityGoogleEnable';
    public const GOOGLE_CLIENT_ID = 'oeSecurityGoogleClientId';
    public const GOOGLE_CLIENT_SECRET = 'oeSecurityGoogleSecret';
    public const GOOGLE_REDIRECT_URL = 'oeSecurityGoogleRedirectUrl';

    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function isFacebookLoginEnabled(): bool
    {
        return $this->moduleSettingService->getBoolean(self::FACEBOOK_LOGIN_ENABLED, Module::MODULE_ID);
    }

    public function saveFacebookEnabled(bool $value): void
    {
        $this->moduleSettingService->saveBoolean(self::FACEBOOK_LOGIN_ENABLED, $value, Module::MODULE_ID);
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

    public function isGoogleActive(): bool
    {
        return $this->moduleSettingService->getBoolean(self::GOOGLE_ACTIVE, Module::MODULE_ID);
    }

    public function getGoogleClientId(): string
    {
        return $this->getStringValue(self::GOOGLE_CLIENT_ID);
    }

    public function getGoogleClientSecret(): string
    {
        return $this->getStringValue(self::GOOGLE_CLIENT_SECRET);
    }

    public function getGoogleRedirectUrl(): string
    {
        return $this->getStringValue(self::GOOGLE_REDIRECT_URL);
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
