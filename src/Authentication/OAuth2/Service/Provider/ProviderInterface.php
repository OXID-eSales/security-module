<?php

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

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
     * Get the authorization URL to redirect the user for login/consent.
     *
     * @param string $state Random CSRF prevention token.
     */
    public function getAuthorizationUrl(string $state): string;

    /**
     * Exchange the authorization code for an access token.
     */
    public function getAccessToken(string $code): AccessTokenInterface;

    /**
     * Fetch user information (claims) from the provider using the access token.
     * Should return standardized data: id, email, name, avatar, etc.
     */
    public function getUserInfo(AccessTokenInterface $token): array;

    /**
     * Validate the provider response.
     */
    public function validateToken(AccessTokenInterface $token): bool;
}
