<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\ProviderNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollector;
use PHPUnit\Framework\TestCase;

class ProviderCollectorTest extends TestCase
{
    public function testGetProviders(): void
    {
        $providers = [
            uniqid(),
            uniqid(),
        ];

        $sut = new ProviderCollector($this->getProviders($providers));
        $result = $sut->getProviders();

        $this->assertSame(
            $providers,
            array_map(fn($item) => $item->getName(), $result)
        );

        $this->assertContainsOnlyInstancesOf(ProviderInterface::class, $result);
    }

    public function testGetExistingProvider()
    {
        $providers = [
            uniqid(),
            uniqid(),
        ];

        $searchProvider = $providers[array_rand($providers)];

        $sut = new ProviderCollector($this->getProviders($providers));
        $result = $sut->getProvider($searchProvider);

        $this->assertInstanceOf(ProviderInterface::class, $result);
        $this->assertSame($searchProvider, $result->getName());
    }

    public function testGetNotExistingProvider()
    {
        $providers = [
            uniqid(),
            uniqid(),
        ];

        $this->expectException(ProviderNotFoundException::class);

        $sut = new ProviderCollector($this->getProviders($providers));
        $sut->getProvider('provider 9');
    }

    private function getProviders(array $providers): array
    {
        $stubs = [];

        foreach ($providers as $provider) {
            $stubs[] = $this->createConfiguredStub(ProviderInterface::class, [
                'getName' => $provider,
            ]);
        }

        return $stubs;
    }
}
