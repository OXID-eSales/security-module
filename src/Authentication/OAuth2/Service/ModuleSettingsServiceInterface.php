<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

interface ModuleSettingsServiceInterface
{
    public function isFacebookLoginEnabled(): bool;

    public function getFacebookClientId(): string;

    public function getFacebookClientSecret(): string;

    public function getFacebookRedirectUrl(): string;

    public function isGoogleActive(): bool;

    public function getGoogleClientId(): string;

    public function getGoogleClientSecret(): string;

    public function getGoogleRedirectUrl(): string;
}
