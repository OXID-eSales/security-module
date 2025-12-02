<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Service;

use ArrayIterator;
use Closure;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\ProviderNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderAdapterInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProviderCollectorTest extends TestCase
{
    // This provider returns factories for each iterable type
    public static function iteratorWrapperDataProvider(): \Generator
    {
        yield 'array' => [fn(array $stubs) => $stubs];
        yield 'ArrayIterator' => [fn(array $stubs) => new ArrayIterator($stubs)];
        yield 'Generator' => [fn(array $stubs) => (function () use ($stubs) {
            yield from $stubs;
        })()];
    }

    #[DataProvider('iteratorWrapperDataProvider')]
    public function testGetProviders(Closure $wrapperFactory): void
    {
        $stub1 = $this->getProviderAdapterStub();
        $stub2 = $this->getProviderAdapterStub();
        $stubs = [$stub1, $stub2];

        $sut = new ProviderCollector($wrapperFactory($stubs));
        $result = $sut->getProviders();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($stubs, $result);
    }

    #[DataProvider('iteratorWrapperDataProvider')]
    public function testGetExistingProvider(Closure $wrapperFactory): void
    {
        $stub1 = $this->getProviderAdapterStub();
        $stub2 = $this->getProviderAdapterStub();
        $stubs = [$stub1, $stub2];
        $searchProvider = $stubs[array_rand($stubs)];

        $sut = new ProviderCollector($wrapperFactory($stubs));
        $result = $sut->getProvider($searchProvider->getName());

        $this->assertSame($searchProvider, $result);
    }

    #[DataProvider('iteratorWrapperDataProvider')]
    public function testGetNotExistingProvider(Closure $wrapperFactory): void
    {
        $stub1 = $this->getProviderAdapterStub();
        $stub2 = $this->getProviderAdapterStub();
        $stubs = [$stub1, $stub2];

        $this->expectException(ProviderNotFoundException::class);

        $sut = new ProviderCollector($wrapperFactory($stubs));
        $sut->getProvider('nonexistent-provider');
    }

    private function getProviderAdapterStub(): ProviderAdapterInterface
    {
        return $this->createConfiguredStub(ProviderAdapterInterface::class, ['getName' => uniqid()]);
    }
}
