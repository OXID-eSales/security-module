<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\NotifierNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\NotifierAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\NotifierCollector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ArrayIterator;
use Closure;

class NotifierCollectorTest extends TestCase
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
    public function testGetExistingNotifier(Closure $wrapperFactory): void
    {
        $stub1 = $this->getNotifierAdapterStub();
        $stub2 = $this->getNotifierAdapterStub();
        $stubs = [$stub1, $stub2];
        $searchNotifier = $stubs[array_rand($stubs)];

        $sut = new NotifierCollector($wrapperFactory($stubs));
        $result = $sut->getNotifier($searchNotifier->getName());

        $this->assertSame($searchNotifier, $result);
    }

    #[DataProvider('iteratorWrapperDataProvider')]
    public function testGetNotExistingNotifier(Closure $wrapperFactory): void
    {
        $stub1 = $this->getNotifierAdapterStub();
        $stub2 = $this->getNotifierAdapterStub();
        $stubs = [$stub1, $stub2];

        $this->expectException(NotifierNotFoundException::class);

        $sut = new NotifierCollector($wrapperFactory($stubs));
        $sut->getNotifier('nonexisting_notifier_name');
    }

    private function getNotifierAdapterStub(): NotifierAdapterInterface
    {
        return $this->createConfiguredStub(NotifierAdapterInterface::class, ['getName' => uniqid()]);
    }
}
