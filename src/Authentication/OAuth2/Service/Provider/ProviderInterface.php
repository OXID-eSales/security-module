<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;

interface ProviderInterface
{
    /**
     * Get the unique identifier of the provider (e.g., 'google', 'facebook').
     */
    public function getName(): string;

    /**
     * Get the underlying OAuth2 client (League provider instance).
     */
    public function getClient(): AbstractProvider;

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
     * Should return standardized data: id, email, name, avatar, etc.
     */
    public function getUserInfo(AccessTokenInterface $token): UserDTOInterface;
}
