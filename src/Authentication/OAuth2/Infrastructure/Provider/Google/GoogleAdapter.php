<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Google;

use League\OAuth2\Client\Provider\Google as GoogleProvider;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTO;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\OAuth2UserDTOFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderAdapterInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use Exception;

class GoogleAdapter implements ProviderAdapterInterface
{
    private GoogleProvider $provider;

    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSettings,
        private readonly GoogleProviderFactoryInterface $googleProvider,
        private readonly OAuth2UserDTOFactoryInterface $oAuth2UserDTOFactory,
    ) {
        $this->provider = $this->googleProvider->create();
    }

    public function getName(): string
    {
        return 'google';
    }

    public function isActive(): bool
    {
        return $this->moduleSettings->isGoogleLoginEnabled();
    }

    public function getAuthorizationUrl(array $options = []): string
    {
        return $this->provider->getAuthorizationUrl($options);
    }

    public function getAccessToken(#[\SensitiveParameter] string $code): AccessTokenInterface
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    public function getUserInfo(AccessTokenInterface $token): OAuth2UserDTOInterface
    {
        if (!$token instanceof AccessToken) {
            throw new Exception('Access token must be an instance of AccessToken.');
        }

        /** @var GoogleUser $user */
        $user = $this->provider->getResourceOwner($token);

        return $this->oAuth2UserDTOFactory->createFromGoogleUser($user);
    }
}
