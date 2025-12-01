<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider;

use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;

interface ProviderAdapterInterface
{
    /**
     * Get the unique identifier of the provider (e.g., 'google', 'facebook').
     */
    public function getName(): string;

    /**
     * Check if the provider is active/enabled.
     */
    public function isActive(): bool;

    /**
     * Get the authorization URL to redirect the user for login/consent.
     */
    public function getAuthorizationUrl(array $options = []): string;

    /**
     * Exchange the authorization code for an access token.
     */
    public function getAccessToken(string $code): AccessTokenInterface;

    /**
     * Fetch user information (claims) from the provider using the access token.
     * Should return UserInterface
     */
    public function getUserInfo(AccessTokenInterface $token): OAuth2UserDTOInterface;
}
