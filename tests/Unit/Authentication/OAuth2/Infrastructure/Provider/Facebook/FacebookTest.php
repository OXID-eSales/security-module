<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Authentication\OAuth2\Infrastructure\Provider\Facebook;

use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook\Facebook;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook\FacebookProviderFactory;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Facebook as FacebookOAuthProvider;

class FacebookTest extends TestCase
{
    public function testProviderActivity(): void
    {
        $isActive = (bool) rand(0, 1);

        $moduleSettingsMock = $this->createMock(ModuleSettingsServiceInterface::class);
        $moduleSettingsMock->method('isFacebookLoginEnabled')
            ->willReturn($isActive);

        $sut = $this->getSut(
            moduleSettings: $moduleSettingsMock
        );

        $this->assertSame($isActive, $sut->isActive());
    }

    public function testProviderName(): void
    {
        $sut = $this->getSut();

        $this->assertSame('facebook', $sut->getName());
    }

    public function testProviderAuthorizeUrl(): void
    {
        $expectedUrl = uniqid();

        $providerInstanceMock = $this->createMock(FacebookOAuthProvider::class);
        $providerInstanceMock->method('getAuthorizationUrl')
            ->willReturn($expectedUrl);

        $providerFactoryMock = $this->createMock(FacebookProviderFactory::class);
        $providerFactoryMock
            ->method('create')
            ->willReturn($providerInstanceMock);

        $sut = $this->getSut(
            facebookProvider: $providerFactoryMock
        );

        $this->assertSame($expectedUrl, $sut->getAuthorizationUrl());
    }

    public function testProviderAccessToken(): void
    {
        $code = uniqid();

        $expectedTokenMock = $this->createMock(AccessTokenInterface::class);

        $providerInstanceMock = $this->createMock(FacebookOAuthProvider::class);
        $providerInstanceMock
            ->method('getAccessToken')
            ->willReturn($expectedTokenMock);

        $providerFactoryMock = $this->createMock(FacebookProviderFactory::class);
        $providerFactoryMock
            ->method('create')
            ->willReturn($providerInstanceMock);

        $sut = $this->getSut(
            facebookProvider: $providerFactoryMock
        );

        $this->assertSame($expectedTokenMock, $sut->getAccessToken($code));
    }

    public function testProviderUserInfo(): void
    {
        $expectedEmail = uniqid();

        $resourceOwnerMock = $this->createMock(FacebookUser::class);
        $resourceOwnerMock
            ->method('getEmail')
            ->willReturn($expectedEmail);

        $providerInstanceMock = $this->createMock(FacebookOAuthProvider::class);
        $providerInstanceMock
            ->method('getResourceOwner')
            ->willReturn($resourceOwnerMock);

        $providerFactoryMock = $this->createMock(FacebookProviderFactory::class);
        $providerFactoryMock
            ->method('create')
            ->willReturn($providerInstanceMock);

        $sut = $this->getSut(
            facebookProvider: $providerFactoryMock
        );

        $accessTokenMock = $this->createMock(AccessToken::class);
        $userInfo = $sut->getUserInfo($accessTokenMock);

        $this->assertSame($expectedEmail, $userInfo->getEmail());
    }

    private function getSut(
        ModuleSettingsServiceInterface $moduleSettings = null,
        FacebookProviderFactory $facebookProvider = null,
    ): Facebook {
        return new Facebook(
            moduleSettings: $moduleSettings ?? $this->createStub(ModuleSettingsServiceInterface::class),
            facebookProvider: $facebookProvider ?? $this->createStub(FacebookProviderFactory::class)
        );
    }
}
