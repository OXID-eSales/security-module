<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\OAuth2\Infrastructure\Provider\Google;

use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Google\GoogleProviderFactory;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Google as GoogleProvider;

class GoogleProviderFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $clientId = uniqid();
        $redirectUrl = uniqid();

        $moduleSettingsStub = $this->createStub(ModuleSettingsServiceInterface::class);
        $moduleSettingsStub->method('getGoogleClientId')->willReturn($clientId);
        $moduleSettingsStub->method('getGoogleRedirectUrl')->willReturn($redirectUrl);

        $sut = new GoogleProviderFactory(
            $moduleSettingsStub
        );
        $googleProvider = $sut->create();

        $authUrl = $googleProvider->getAuthorizationUrl();

        $this->assertInstanceOf(GoogleProvider::class, $googleProvider);
        $this->assertStringContainsString('client_id=' . $clientId, $authUrl);
        $this->assertStringContainsString('redirect_uri=' . $redirectUrl, $authUrl);
    }
}
