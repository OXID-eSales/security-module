<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectService;
use PHPUnit\Framework\TestCase;

class InternalRedirectServiceTest extends TestCase
{
    public function testGetRedirectUrlReturnsStoredUrlWhenInternal(): void
    {
        $shopUrl = 'https://shop.example.com/';
        $storedUrl = $shopUrl . 'some/page';

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('get')
            ->with(InternalRedirectService::AUTH_REDIRECT_URL)
            ->willReturn($storedUrl);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopUrl')->willReturn($shopUrl);
        $configStub->method('getSslShopUrl')->willReturn($shopUrl);

        $sut = new InternalRedirectService($sessionMock, $configStub);

        $this->assertSame($storedUrl, $sut->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsStoredUrlWhenMatchesSslShopUrl(): void
    {
        $shopUrl = 'http://shop.example.com/';
        $sslShopUrl = 'https://shop.example.com/';
        $storedUrl = $sslShopUrl . 'some/page';

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('get')
            ->with(InternalRedirectService::AUTH_REDIRECT_URL)
            ->willReturn($storedUrl);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopUrl')->willReturn($shopUrl);
        $configStub->method('getSslShopUrl')->willReturn($sslShopUrl);

        $sut = new InternalRedirectService($sessionMock, $configStub);

        $this->assertSame($storedUrl, $sut->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsShopHomeWhenStoredUrlIsExternal(): void
    {
        $shopUrl = 'https://shop.example.com/';
        $shopHomeUrl = 'https://shop.example.com/index.php?';
        $externalUrl = 'https://evil.example.com/phishing';

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('get')
            ->with(InternalRedirectService::AUTH_REDIRECT_URL)
            ->willReturn($externalUrl);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopUrl')->willReturn($shopUrl);
        $configStub->method('getSslShopUrl')->willReturn($shopUrl);
        $configStub->method('getShopHomeUrl')->willReturn($shopHomeUrl);

        $sut = new InternalRedirectService($sessionMock, $configStub);

        $this->assertSame($shopHomeUrl, $sut->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsShopHomeWhenNoStoredUrl(): void
    {
        $shopHomeUrl = 'https://shop.example.com/index.php?';

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('get')
            ->with(InternalRedirectService::AUTH_REDIRECT_URL)
            ->willReturn(null);

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn($shopHomeUrl);

        $sut = new InternalRedirectService($sessionMock, $configStub);

        $this->assertSame($shopHomeUrl, $sut->getRedirectUrl());
    }

    public function testGetRedirectUrlReturnsShopHomeWhenStoredUrlIsEmpty(): void
    {
        $shopHomeUrl = 'https://shop.example.com/index.php?';

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('get')
            ->with(InternalRedirectService::AUTH_REDIRECT_URL)
            ->willReturn('');

        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn($shopHomeUrl);

        $sut = new InternalRedirectService($sessionMock, $configStub);

        $this->assertSame($shopHomeUrl, $sut->getRedirectUrl());
    }
}
