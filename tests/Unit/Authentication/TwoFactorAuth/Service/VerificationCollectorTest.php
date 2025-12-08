<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\VerificatorNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\VerificatorAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\VerificationCollectorService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ArrayIterator;
use Closure;

class VerificationCollectorTest extends TestCase
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
        $searchVerificator = $stubs[array_rand($stubs)];

        $sut = new VerificationCollectorService($wrapperFactory($stubs));
        $result = $sut->getVerificator($searchVerificator->getName());

        $this->assertSame($searchVerificator, $result);
    }

    #[DataProvider('iteratorWrapperDataProvider')]
    public function testGetNotExistingNotifier(Closure $wrapperFactory): void
    {
        $stub1 = $this->getNotifierAdapterStub();
        $stub2 = $this->getNotifierAdapterStub();
        $stubs = [$stub1, $stub2];

        $this->expectException(VerificatorNotFoundException::class);

        $sut = new VerificationCollectorService($wrapperFactory($stubs));
        $sut->getVerificator('nonexisting_verificator_name');
    }

    private function getNotifierAdapterStub(): VerificatorAdapterInterface
    {
        return $this->createConfiguredStub(VerificatorAdapterInterface::class, ['getName' => uniqid()]);
    }
}
