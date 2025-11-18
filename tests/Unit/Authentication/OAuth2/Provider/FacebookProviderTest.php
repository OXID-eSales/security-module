<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Provider;

use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\Facebook\Facebook as FacebookProvider;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Facebook;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserDataType;

class FacebookProviderTest extends TestCase
{
    public function testGetNameReturnsFacebook(): void
    {
        $settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $provider = new FacebookProvider($settings);
        $this->assertSame('facebook', $provider->getName());
    }

    public function testGetClientCreatesFacebookProvider(): void
    {
        $settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $settings->method('getFacebookClientId')->willReturn('id123');
        $settings->method('getFacebookClientSecret')->willReturn('secret123');
        $settings->method('getFacebookRedirectUrl')->willReturn('http://redirect');

        $provider = new FacebookProvider($settings);
        $google = new Facebook([
            'clientId'     => 'id123',
            'clientSecret' => 'secret123',
            'redirectUri'  => 'http://redirect',
            'graphApiVersion' => 'v2.10'
        ]);

        $client = $provider->getClient();
        $this->assertEquals($google, $client);
    }

    public function testIsActiveDelegatesToSettings(): void
    {
        $settings = $this->createMock(ModuleSettingsServiceInterface::class);
        $settings->method('isFacebookActive')->willReturn(true);
        $provider = new FacebookProvider($settings);
        $this->assertTrue($provider->isActive());
    }
}
