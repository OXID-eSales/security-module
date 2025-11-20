<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Authentication\OAuth2\Infrastructure\Provider\Facebook;

use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook\FacebookProviderFactory;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Facebook as FacebookProvider;

class FacebookFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $facebookProviderFactory = new FacebookProviderFactory(
            $this->createMock(ModuleSettingsServiceInterface::class)
        );
        $facebookProvider = $facebookProviderFactory->create();

        $this->assertInstanceOf(FacebookProvider::class, $facebookProvider);
    }
}
