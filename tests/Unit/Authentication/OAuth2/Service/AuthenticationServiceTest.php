<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Service;

use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderAdapterInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\AuthenticationService;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollectorInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;

class AuthenticationServiceTest extends TestCase
{
    public function testGetAuthorizationUrlResolvesProviderAndReturnsUrl(): void
    {
        $providerName = uniqid();
        $authorizationUrl = uniqid();

        $providerStub = $this->createStub(ProviderAdapterInterface::class);
        $providerStub->method('getAuthorizationUrl')->willReturn($authorizationUrl);

        $providerCollectorStub = $this->createStub(ProviderCollectorInterface::class);
        $providerCollectorStub->method('getProvider')
            ->with($providerName)
            ->willReturn($providerStub);

        $sut = new AuthenticationService(
            providerCollector: $providerCollectorStub,
            userService: $this->createStub(UserServiceInterface::class),
        );

        $this->assertSame($authorizationUrl, $sut->getAuthorizationUrl($providerName));
    }

    public function testHandleCallbackExchangesCodeAndLogsInUser(): void
    {
        $providerName = uniqid();
        $code = uniqid();

        $accessTokenStub = $this->createStub(AccessTokenInterface::class);
        $userDTOStub = $this->createStub(OAuth2UserDTOInterface::class);

        $providerStub = $this->createStub(ProviderAdapterInterface::class);
        $providerStub->method('getAccessToken')
            ->with($code)
            ->willReturn($accessTokenStub);
        $providerStub->method('getUserInfo')
            ->with($accessTokenStub)
            ->willReturn($userDTOStub);

        $providerCollectorStub = $this->createStub(ProviderCollectorInterface::class);
        $providerCollectorStub->method('getProvider')
            ->with($providerName)
            ->willReturn($providerStub);

        $userServiceSpy = $this->createMock(UserServiceInterface::class);
        $userServiceSpy->expects($this->once())
            ->method('login')
            ->with($userDTOStub);

        $sut = new AuthenticationService(
            providerCollector: $providerCollectorStub,
            userService: $userServiceSpy,
        );

        $sut->handleCallback($providerName, $code);
    }
}
