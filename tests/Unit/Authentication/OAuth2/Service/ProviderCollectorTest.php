<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\ProviderInterface;
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
            array_map(fn($provider) => $provider->getName(), $result)
        );

        $this->assertContainsOnlyInstancesOf(ProviderInterface::class, $result);
    }

    private function getProviders(): array
    {
        $provider1 = $this->createConfiguredMock(ProviderInterface::class, [
            'getName' => 'provider 1',
        ]);
        $provider2 = $this->createConfiguredMock(ProviderInterface::class, [
            'getName' => 'provider 2',
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
