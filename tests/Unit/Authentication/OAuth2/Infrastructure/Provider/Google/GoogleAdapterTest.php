<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Infrastructure\Provider\Google;

use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Google\GoogleProviderFactory;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Google\GoogleAdapter;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\OAuth2UserDTOFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Google as GoogleOAuthProvider;

class GoogleAdapterTest extends TestCase
{
    public function testProviderActivity(): void
    {
        $isActive = (bool) rand(0, 1);

        $moduleSettingsMock = $this->createMock(ModuleSettingsServiceInterface::class);
        $moduleSettingsMock->method('isGoogleLoginEnabled')
            ->willReturn($isActive);

        $sut = $this->getSut(
            moduleSettings: $moduleSettingsMock
        );

        $this->assertSame($isActive, $sut->isActive());
    }

    public function testProviderName(): void
    {
        $sut = $this->getSut();

        $this->assertSame('google', $sut->getName());
    }

    public function testProviderAuthorizeUrl(): void
    {
        $expectedUrl = uniqid();

        $providerInstanceMock = $this->createMock(GoogleOAuthProvider::class);
        $providerInstanceMock->method('getAuthorizationUrl')
            ->willReturn($expectedUrl);

        $providerFactoryMock = $this->createMock(GoogleProviderFactory::class);
        $providerFactoryMock
            ->method('create')
            ->willReturn($providerInstanceMock);

        $sut = $this->getSut(
            googleProvider: $providerFactoryMock
        );

        $this->assertSame($expectedUrl, $sut->getAuthorizationUrl());
    }

    public function testProviderAccessToken(): void
    {
        $code = uniqid();

        $expectedTokenStub = $this->createStub(AccessTokenInterface::class);

        $providerInstanceMock = $this->createMock(GoogleOAuthProvider::class);
        $providerInstanceMock
            ->method('getAccessToken')
            ->willReturn($expectedTokenStub);

        $providerFactoryMock = $this->createMock(GoogleProviderFactory::class);
        $providerFactoryMock
            ->method('create')
            ->willReturn($providerInstanceMock);

        $sut = $this->getSut(
            googleProvider: $providerFactoryMock
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

        $googleUserStub = $this->createStub(GoogleUser::class);

        $providerInstanceMock = $this->createMock(GoogleOAuthProvider::class);
        $providerInstanceMock
            ->method('getResourceOwner')
            ->with($accessTokenStub)
            ->willReturn($googleUserStub);

        $providerFactoryStub = $this->createStub(GoogleProviderFactory::class);
        $providerFactoryStub
            ->method('create')
            ->willReturn($providerInstanceMock);

        $expectedUserDTOStub = $this->createStub(OAuth2UserDTOInterface::class);

        $oAuth2UserDTOFactoryMock = $this->createMock(OAuth2UserDTOFactoryInterface::class);
        $oAuth2UserDTOFactoryMock
            ->method('createFromGoogleUser')
            ->with($googleUserStub)
            ->willReturn($expectedUserDTOStub);

        $sut = $this->getSut(
            googleProvider: $providerFactoryStub,
            oAuth2UserDTOFactory: $oAuth2UserDTOFactoryMock
        );

        $result = $sut->getUserInfo($accessTokenStub);

        $this->assertSame($expectedUserDTOStub, $result);
    }

    private function getSut(
        ModuleSettingsServiceInterface $moduleSettings = null,
        GoogleProviderFactory $googleProvider = null,
        OAuth2UserDTOFactoryInterface $oAuth2UserDTOFactory = null,
    ): GoogleAdapter {
        return new GoogleAdapter(
            moduleSettings: $moduleSettings ?? $this->createStub(ModuleSettingsServiceInterface::class),
            googleProvider: $googleProvider ?? $this->createStub(GoogleProviderFactory::class),
            oAuth2UserDTOFactory: $oAuth2UserDTOFactory ?? $this->createStub(OAuth2UserDTOFactoryInterface::class),
        );
    }
}
