<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\ProviderInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Exception\ProviderNotFound;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollector;
use PHPUnit\Framework\TestCase;

class ProviderCollectorTest extends TestCase
{
    public function testGetProviders(): void
    {
        $sut = new ProviderCollector($this->getProviders());
        $result = $sut->getProviders();

        $this->assertSame(
            [
                'provider 1',
                'provider 2',
                'provider 3'
            ],
            array_keys($result)
        );

        $this->assertContainsOnlyInstancesOf(ProviderInterface::class, $result);
    }

    public function testGetExistingProvider()
    {
        $sut = new ProviderCollector($this->getProviders());
        $result = $sut->getProvider('provider 2');

        $this->assertInstanceOf(ProviderInterface::class, $result);
        $this->assertSame('provider 2', $result->getName());
    }

    public function testGetNotExistingProvider()
    {
        $this->expectException(ProviderNotFound::class);

        $sut = new ProviderCollector($this->getProviders());
        $sut->getProvider('provider 9');
    }

    private function getProviders(): array
    {
        $provider1 = $this->createConfiguredMock(ProviderInterface::class, [
            'getName' => 'provider 1',
        ]);
        $provider2 = $this->createConfiguredMock(ProviderInterface::class, [
            'getName' => 'provider 2',
            'isActive' => true,
        ]);
        $provider3 = $this->createConfiguredMock(ProviderInterface::class, [
            'getName' => 'provider 3',
        ]);

        return [
            $provider1,
            $provider2,
            $provider3
        ];
    }
}
