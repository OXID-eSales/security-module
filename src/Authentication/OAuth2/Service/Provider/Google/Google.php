<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\Google;

use League\OAuth2\Client\Provider\Google as GoogleProvider;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTO;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\ProviderInterface;

class Google implements ProviderInterface
{
    private GoogleProvider $client;

    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSettings,
    ) {
    }

    public function getName(): string
    {
        return 'google';
    }

    public function getClient(): GoogleProvider
    {
        return $this->client = new GoogleProvider([
            'clientId'     => $this->moduleSettings->getGoogleClientId(),
            'clientSecret' => $this->moduleSettings->getGoogleClientSecret(),
            'redirectUri'  => $this->moduleSettings->getGoogleRedirectUrl(),
        ]);
    }

    public function isActive(): bool
    {
        return $this->moduleSettings->isGoogleActive();
    }

    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->client->getAuthorizationUrl($options);
    }

    public function getAccessToken(string $code): AccessTokenInterface
    {
        return $this->client->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    public function getUserInfo(AccessTokenInterface $token): UserDTOInterface
    {
        /** @var AccessToken $token */
        $user = $this->client->getResourceOwner($token);

        /** @var GoogleUser $user */
        return new UserDTO(
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
        );
    }
}
