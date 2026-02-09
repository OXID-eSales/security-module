<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

interface AuthenticationServiceInterface
{
    public function getAuthorizationUrl(string $providerName): string;

    public function handleCallback(string $providerName, #[\SensitiveParameter] string $code): void;
}
