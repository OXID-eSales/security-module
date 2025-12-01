<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook;

use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTO;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderAdapterInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use League\OAuth2\Client\Provider\Facebook as FacebookProvider;
use Exception;

class FacebookAdapter implements ProviderAdapterInterface
{
    private FacebookProvider $provider;

    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSettings,
        private readonly FacebookProviderFactoryInterface $facebookProvider,
    ) {
        $this->provider = $this->facebookProvider->create();
    }

    public function isActive(): bool
    {
        return $this->moduleSettings->isFacebookLoginEnabled();
    }

    public function getName(): string
    {
        return 'facebook';
    }

    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->provider->getAuthorizationUrl($options);
    }

    public function getAccessToken(string $code): AccessTokenInterface
    {
        return $this->provider->getAccessToken('authorization_code', ['code' => $code]);
    }

    public function getUserInfo(AccessTokenInterface $token): OAuth2UserDTOInterface
    {
        if (!$token instanceof AccessToken) {
            throw new Exception('Access token must be an instance of AccessToken.');
        }

        /** @var FacebookUser $user */
        $user = $this->provider->getResourceOwner($token);

        return new OAuth2UserDTO(
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
        );
    }
}
