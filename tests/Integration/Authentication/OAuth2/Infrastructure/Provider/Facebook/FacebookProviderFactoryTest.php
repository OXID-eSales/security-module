<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\OAuth2\Infrastructure\Provider\Facebook;

use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook\FacebookProviderFactory;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Facebook as FacebookProvider;

class FacebookProviderFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $clientId = uniqid();
        $redirectUrl = uniqid();

        $moduleSettingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $moduleSettingsStub->method('getFacebookClientId')->willReturn($clientId);
        $moduleSettingsStub->method('getFacebookRedirectUrl')->willReturn($redirectUrl);

        $sut = new FacebookProviderFactory(
            $moduleSettingsStub
        );
        $facebookProvider = $sut->create();

        $authUrl = $facebookProvider->getAuthorizationUrl();

        $this->assertInstanceOf(FacebookProvider::class, $facebookProvider);
        $this->assertStringContainsString('client_id=' . $clientId, $authUrl);
        $this->assertStringContainsString('redirect_uri=' . $redirectUrl, $authUrl);
    }
}
