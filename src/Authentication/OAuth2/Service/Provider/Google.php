<?php

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider;

use League\OAuth2\Client\Provider\Google as GoogleProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;

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
        return new GoogleProvider([
            'clientId'     => $this->moduleSettings->getGoogleClientId(),
            'clientSecret' => $this->moduleSettings->getGoogleClientSecret(),
            'redirectUri'  => $this->moduleSettings->getGoogleRedirectUrl(),
        ]);
    }

    public function isActive(): bool
    {
        return true; //todo: add admin setting
    }

    public function getAuthorizationUrl(string $state): string
    {
        return $this->client->getAuthorizationUrl([
            'scope' => ['openid', 'email', 'profile'],
            'state' => $state,
        ]);
    }

    public function getAccessToken(string $code): AccessTokenInterface
    {
        return $this->client->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    public function getUserInfo(AccessTokenInterface $token): array
    {
        $user = $this->client->getResourceOwner($token);

        return [
            'id'     => $user->getId(),
            'email'  => $user->getEmail(),
            'name'   => $user->getName(),
            'avatar' => $user->getAvatar(),
        ];
    }

    public function validateToken(AccessTokenInterface $token): bool
    {
        return !$token->hasExpired();
    }
}
