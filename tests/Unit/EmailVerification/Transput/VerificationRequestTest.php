<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Transput;

use Generator;
use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\EmailVerification\Transput\VerificationRequest;
use OxidEsales\SecurityModule\EmailVerification\Transput\VerificationRequestInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VerificationRequestTest extends TestCase
{
    #[Test]
    public function getToken(): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('get')->with('token')->willReturn($token = bin2hex(random_bytes(32)));

        $sut = $this->getSut(request: $requestMock);

        $this->assertSame($token, $sut->getToken());
    }

    #[Test]
    #[DataProvider('malformedTokenDataProvider')]
    public function getTokenReturnsAnEmptyStringForAMalformedToken(mixed $token): void
    {
        $requestStub = $this->createConfiguredStub(RequestInterface::class, ['get' => $token]);

        $sut = $this->getSut(request: $requestStub);

        $this->assertSame('', $sut->getToken());
    }

    public static function malformedTokenDataProvider(): Generator
    {
        yield 'missing' => [null];
        yield 'empty' => [''];
        yield 'not hex' => ['<script>' . uniqid()];
        yield 'array' => [[uniqid()]];
    }

    #[Test]
    public function isSentStateRequested(): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('get')->with('state')->willReturn('sent');

        $sut = $this->getSut(request: $requestMock);

        $this->assertTrue($sut->isSentStateRequested());
    }

    #[Test]
    #[DataProvider('otherStateDataProvider')]
    public function isSentStateRequestedReturnsFalseForOtherStates(mixed $state): void
    {
        $requestStub = $this->createConfiguredStub(RequestInterface::class, ['get' => $state]);

        $sut = $this->getSut(request: $requestStub);

        $this->assertFalse($sut->isSentStateRequested());
    }

    public static function otherStateDataProvider(): Generator
    {
        yield 'missing' => [null];
        yield 'pending' => ['pending'];
        yield 'verified' => ['verified'];
        yield 'unknown' => [uniqid()];
    }

    private function getSut(?RequestInterface $request = null): VerificationRequestInterface
    {
        return new VerificationRequest(
            request: $request ?? $this->createStub(RequestInterface::class),
        );
    }
}
