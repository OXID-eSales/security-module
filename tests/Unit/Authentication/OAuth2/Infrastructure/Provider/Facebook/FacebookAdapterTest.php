<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Infrastructure\Provider\Facebook;

use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook\FacebookAdapter;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook\FacebookProviderFactory;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\OAuth2UserDTOFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Facebook as FacebookOAuthProvider;

class FacebookAdapterTest extends TestCase
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

        $expectedTokenStub = $this->createStub(AccessTokenInterface::class);

        $providerInstanceMock = $this->createMock(FacebookOAuthProvider::class);
        $providerInstanceMock
            ->method('getAccessToken')
            ->willReturn($expectedTokenStub);

        $providerFactoryMock = $this->createMock(FacebookProviderFactory::class);
        $providerFactoryMock
            ->method('create')
            ->willReturn($providerInstanceMock);

        $sut = $this->getSut(
            facebookProvider: $providerFactoryMock
        );

        $this->assertSame($expectedTokenStub, $sut->getAccessToken($code));
    }

    public function testProviderGetUserInfoThrowException(): void
    {
        $sut = $this->getSut();

        $this->expectException(\Exception::class);

        $sut->getUserInfo($this->createStub(AccessTokenInterface::class));
    }

    public function testProviderUserInfo(): void
    {
        $accessTokenStub = $this->createStub(AccessToken::class);

        $facebookUserStub = $this->createStub(FacebookUser::class);

        $providerInstanceMock = $this->createMock(FacebookOAuthProvider::class);
        $providerInstanceMock
            ->method('getResourceOwner')
            ->with($accessTokenStub)
            ->willReturn($facebookUserStub);

        $providerFactoryStub = $this->createStub(FacebookProviderFactory::class);
        $providerFactoryStub
            ->method('create')
            ->willReturn($providerInstanceMock);

        $expectedUserDTOStub = $this->createStub(OAuth2UserDTOInterface::class);

        $oAuth2UserDTOFactoryMock = $this->createMock(OAuth2UserDTOFactoryInterface::class);
        $oAuth2UserDTOFactoryMock
            ->method('createFromFacebookUser')
            ->with($facebookUserStub)
            ->willReturn($expectedUserDTOStub);

        $sut = $this->getSut(
            facebookProvider: $providerFactoryStub,
            oAuth2UserDTOFactory: $oAuth2UserDTOFactoryMock
        );

        $result = $sut->getUserInfo($accessTokenStub);

        $this->assertSame($expectedUserDTOStub, $result);
    }

    private function getSut(
        ModuleSettingsServiceInterface $moduleSettings = null,
        FacebookProviderFactory $facebookProvider = null,
        OAuth2UserDTOFactoryInterface $oAuth2UserDTOFactory = null,
    ): FacebookAdapter {
        return new FacebookAdapter(
            moduleSettings: $moduleSettings ?? $this->createStub(ModuleSettingsServiceInterface::class),
            facebookProvider: $facebookProvider ?? $this->createStub(FacebookProviderFactory::class),
            oAuth2UserDTOFactory: $oAuth2UserDTOFactory ?? $this->createStub(OAuth2UserDTOFactoryInterface::class),
        );
    }
}
