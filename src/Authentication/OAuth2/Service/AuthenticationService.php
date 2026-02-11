<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

readonly class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private ProviderCollectorInterface $providerCollector,
        private UserServiceInterface $userService,
    ) {
    }

    public function getAuthorizationUrl(string $providerName): string
    {
        $provider = $this->providerCollector->getProvider($providerName);

        return $provider->getAuthorizationUrl();
    }

    public function handleCallback(string $providerName, #[\SensitiveParameter] string $code): void
    {
        $provider = $this->providerCollector->getProvider($providerName);

        $accessToken = $provider->getAccessToken($code);
        $userDTO = $provider->getUserInfo($accessToken);

        $this->userService->login($userDTO);
    }
}
