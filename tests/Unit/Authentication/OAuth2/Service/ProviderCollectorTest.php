<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\ProviderNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderAdapterInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollector;
use PHPUnit\Framework\TestCase;

class ProviderCollectorTest extends TestCase
{
    public function testGetProviders(): void
    {
        $provider1 = $this->createConfiguredMock(ProviderAdapterInterface::class, [
            'getName' => uniqid(),
        ]);
        $provider2 = $this->createConfiguredMock(ProviderAdapterInterface::class, [
            'getName' => uniqid(),
        ]);

        $providers = [$provider1, $provider2];
        $sut = new ProviderCollector($providers);
        $result = $sut->getProviders();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($providers, $result);
    }

    public function testGetExistingProvider()
    {
        $provider1 = $this->createConfiguredMock(ProviderAdapterInterface::class, [
            'getName' => uniqid(),
        ]);
        $provider2 = $this->createConfiguredMock(ProviderAdapterInterface::class, [
            'getName' => uniqid(),
        ]);

        $providers = [$provider1, $provider2];

        $searchProvider = $providers[array_rand($providers)];

        $sut = new ProviderCollector($providers);
        $result = $sut->getProvider($searchProvider->getName());

        $this->assertSame($searchProvider, $result);
    }

    public function testGetNotExistingProvider()
    {
        $provider1 = $this->createConfiguredMock(ProviderAdapterInterface::class, [
            'getName' => uniqid(),
        ]);
        $provider2 = $this->createConfiguredMock(ProviderAdapterInterface::class, [
            'getName' => uniqid(),
        ]);

        $this->expectException(ProviderNotFoundException::class);

        $sut = new ProviderCollector([$provider1, $provider2]);
        $sut->getProvider('provider 9');
    }
}
