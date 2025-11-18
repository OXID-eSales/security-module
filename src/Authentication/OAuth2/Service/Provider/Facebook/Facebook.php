<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\Facebook;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Provider\Facebook as FacebookProvider;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTO;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\ProviderInterface;

class Facebook implements ProviderInterface
{
    private FacebookProvider $facebookProvider;

    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSettings,
    ) {
    }

    public function getName(): string
    {
        return 'facebook';
    }

    public function getClient(): AbstractProvider
    {
        $this->facebookProvider = new FacebookProvider([
            'clientId'        => $this->moduleSettings->getFacebookClientId(),
            'clientSecret'    => $this->moduleSettings->getFacebookClientSecret(),
            'redirectUri'     => $this->moduleSettings->getFacebookRedirectUrl(),
            'graphApiVersion' => 'v2.10',
        ]);

        return $this->facebookProvider;
    }

    public function isActive(): bool
    {
        return $this->moduleSettings->isFacebookActive();
    }

    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->facebookProvider->getAuthorizationUrl($options);
    }

    public function getAccessToken(string $code): AccessTokenInterface
    {
        return $this->facebookProvider->getAccessToken('authorization_code', ['code' => $code]);
    }

    public function getUserInfo(AccessTokenInterface $token): UserDTOInterface
    {
        /** @var AccessToken $token */
        $user = $this->facebookProvider->getResourceOwner($token);

        return new UserDTO(
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
        );
    }
}
