<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\Google\Google as GoogleProvider;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Google;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserDataType;

class GoogleProviderTest extends TestCase
{
    public function testGetNameReturnsGoogle(): void
    {
        $settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $provider = new GoogleProvider($settings);
        $this->assertSame('google', $provider->getName());
    }

    public function testGetClientCreatesGoogleProvider(): void
    {
        $settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $settings->method('getGoogleClientId')->willReturn('id123');
        $settings->method('getGoogleClientSecret')->willReturn('secret123');
        $settings->method('getGoogleRedirectUrl')->willReturn('http://redirect');

        $provider = new GoogleProvider($settings);
        $google = new Google([
            'clientId'     => 'id123',
            'clientSecret' => 'secret123',
            'redirectUri'  => 'http://redirect',
        ]);

        $client = $provider->getClient();
        $this->assertEquals($google, $client);
    }

    public function testIsActiveDelegatesToSettings(): void
    {
        $settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $settings->method('isGoogleActive')->willReturn(true);
        $provider = new GoogleProvider($settings);
        $this->assertTrue($provider->isActive());
    }
}
