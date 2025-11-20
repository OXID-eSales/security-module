<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook;

use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\User;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;

class Facebook implements ProviderInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSettings,
        private readonly FacebookProviderFactory $facebookProvider,
    ) {
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
        return $this->facebookProvider->create()->getAuthorizationUrl($options);
    }

    public function getAccessToken(string $code): AccessTokenInterface
    {
        return $this->facebookProvider->create()->getAccessToken('authorization_code', ['code' => $code]);
    }

    public function getUserInfo(AccessTokenInterface $token): UserInterface
    {
        /** @var FacebookUser $user */
        // @phpstan-ignore-next-line Ignored since AccessTokenInterface is not recognized as AccessToken
        $user = $this->facebookProvider->create()->getResourceOwner($token);

        return new User(
            $user->getEmail(),
        );
    }
}
