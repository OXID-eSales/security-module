<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\Session\SessionKeys;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testHandleLoginSetsSessionAndRedirects(): void
    {
        $userId = uniqid();
        $url = uniqid();

        $authorizeServiceSpy = $this->createMock(AuthorizeServiceInterface::class);
        $authorizeServiceSpy->expects($this->once())
            ->method('generate');
        $authorizeServiceSpy->expects($this->once())
            ->method('getVerificationUrl')
            ->willReturn($url);

        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->once())
            ->method('set')
            ->with(AuthorizeService::USER_SESSION_KEY, $userId);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($url);

        $sut = $this->getSut(
            authorizeService: $authorizeServiceSpy,
            session: $sessionSpy,
            utils: $utilsSpy,
        );

        $sut->handleLogin($userId);
    }

    public function testClearOTPSessionVariablesRemovesAllKeys(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);

        $expectedRemovals = [
            AuthorizeService::USER_SESSION_KEY,
            SessionKeys::AUTH_REDIRECT_URL,
            'OTP_PASS',
        ];

        $sessionMock->expects($this->exactly(3))
            ->method('remove')
            ->willReturnCallback(function ($key) use (&$expectedRemovals) {
                $this->assertContains($key, $expectedRemovals);
                $expectedRemovals = array_filter($expectedRemovals, fn($k) => $k !== $key);
            });

        $sut = $this->getSut(
            session: $sessionMock,
        );

        $sut->clearOTPSessionVariables();

        $this->assertEmpty($expectedRemovals, 'All session keys should be removed');
    }

    public function testFinalizeLoginLoadsUserAndPerformsLogin(): void
    {
        $userId = uniqid();
        $userName = uniqid();
        $shopHomeUrl = uniqid();

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')
            ->willReturnCallback(function ($key) use ($userId) {
                if ($key === AuthorizeService::USER_SESSION_KEY) {
                    return $userId;
                }
                return null;
            });

        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->once())
            ->method('load')
            ->with($userId);
        $userSpy->expects($this->once())
            ->method('getFieldData')
            ->with('oxusername')
            ->willReturn($userName);
        $userSpy->expects($this->once())
            ->method('login')
            ->with($userName, null, false);

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userSpy);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn($shopHomeUrl);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($shopHomeUrl, false);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
            session: $sessionStub,
            utils: $utilsSpy,
            config: $configStub,
        );

        $sut->finalizeLogin();
    }

    public function testFinalizeLoginSetsOTPPassInSession(): void
    {
        $userId = uniqid();

        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->method('get')
            ->willReturnCallback(function ($key) use ($userId) {
                if ($key === AuthorizeService::USER_SESSION_KEY) {
                    return $userId;
                }
                return null;
            });

        $sessionSpy->expects($this->once())
            ->method('set')
            ->with('OTP_PASS', $userId);

        $userStub = $this->createStub(User::class);
        $userStub->method('getFieldData')->willReturn(uniqid());

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userStub);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn(uniqid());

        $utilsStub = $this->createStub(Utils::class);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
            session: $sessionSpy,
            utils: $utilsStub,
            config: $configStub,
        );

        $sut->finalizeLogin();
    }

    public function testFinalizeLoginRedirectsToStoredUrlWhenInternal(): void
    {
        $userId = uniqid();
        $shopUrl = uniqid();
        $storedUrl = $shopUrl . uniqid();

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')
            ->willReturnCallback(function ($key) use ($userId, $storedUrl) {
                if ($key === AuthorizeService::USER_SESSION_KEY) {
                    return $userId;
                }
                if ($key === SessionKeys::AUTH_REDIRECT_URL) {
                    return $storedUrl;
                }
                return null;
            });

        $userStub = $this->createStub(User::class);
        $userStub->method('getFieldData')->willReturn(uniqid());

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userStub);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopUrl')->willReturn($shopUrl);
        $configStub->method('getSslShopUrl')->willReturn($shopUrl);
        $configStub->method('getShopHomeUrl')->willReturn($shopUrl);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($storedUrl, false);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
            session: $sessionStub,
            utils: $utilsSpy,
            config: $configStub,
        );

        $sut->finalizeLogin();
    }

    public function testFinalizeLoginRedirectsToShopHomeWhenStoredUrlIsExternal(): void
    {
        $userId = uniqid();
        $shopUrl = uniqid();
        $externalUrl = uniqid();

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')
            ->willReturnCallback(function ($key) use ($userId, $externalUrl) {
                if ($key === AuthorizeService::USER_SESSION_KEY) {
                    return $userId;
                }
                if ($key === SessionKeys::AUTH_REDIRECT_URL) {
                    return $externalUrl;
                }
                return null;
            });

        $userStub = $this->createStub(User::class);
        $userStub->method('getFieldData')->willReturn('user@example.com');

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userStub);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopUrl')->willReturn($shopUrl);
        $configStub->method('getSslShopUrl')->willReturn($shopUrl);
        $configStub->method('getShopHomeUrl')->willReturn($shopUrl);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($shopUrl, false);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
            session: $sessionStub,
            utils: $utilsSpy,
            config: $configStub,
        );

        $sut->finalizeLogin();
    }

    public function testFinalizeLoginRedirectsToShopHomeWhenNoStoredUrl(): void
    {
        $userId = uniqid();
        $shopHomeUrl = uniqid();

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')
            ->willReturnCallback(function ($key) use ($userId) {
                if ($key === AuthorizeService::USER_SESSION_KEY) {
                    return $userId;
                }
                return null;
            });

        $userStub = $this->createStub(User::class);
        $userStub->method('getFieldData')->willReturn('user@example.com');

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userStub);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn($shopHomeUrl);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($shopHomeUrl, false);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
            session: $sessionStub,
            utils: $utilsSpy,
            config: $configStub,
        );

        $sut->finalizeLogin();
    }

    public function getSut(
        AuthorizeServiceInterface $authorizeService = null,
        UserFactoryInterface $userFactory = null,
        SessionInterface $session = null,
        Utils $utils = null,
        Config $config = null,
    ): UserServiceInterface {
        return new UserService(
            authorizeService: $authorizeService ?? $this->createStub(AuthorizeServiceInterface::class),
            userFactory: $userFactory ?? $this->createStub(UserFactoryInterface::class),
            session: $session ?? $this->createStub(SessionInterface::class),
            utils: $utils ?? $this->createStub(Utils::class),
            config: $config ?? $this->createStub(Config::class),
        );
    }
}
