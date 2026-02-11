<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Transput;

use OxidEsales\Eshop\Core\Request as ShopRequest;
use OxidEsales\SecurityModule\Authentication\OAuth2\Transput\OAuthRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OAuthRequestTest extends TestCase
{
    #[DataProvider('providerDataProvider')]
    public function testGetProvider($paramValue, string $expected): void
    {
        $requestStub = $this->createRequestStub(['provider' => $paramValue]);

        $sut = new OAuthRequest($requestStub);

        $this->assertSame($expected, $sut->getProvider());
    }

    public static function providerDataProvider(): iterable
    {
        yield 'google' => ['google', 'google'];
        yield 'facebook' => ['facebook', 'facebook'];
        yield 'null returns empty string' => [null, ''];
        yield 'empty string' => ['', ''];
    }

    #[DataProvider('codeDataProvider')]
    public function testGetCode($paramValue, string $expected): void
    {
        $requestStub = $this->createRequestStub(['code' => $paramValue]);

        $sut = new OAuthRequest($requestStub);

        $this->assertSame($expected, $sut->getCode());
    }

    public static function codeDataProvider(): iterable
    {
        yield 'valid code' => ['abc123', 'abc123'];
        yield 'null returns empty string' => [null, ''];
        yield 'empty string' => ['', ''];
    }

    #[DataProvider('errorDataProvider')]
    public function testHasError($paramValue, bool $expected): void
    {
        $requestStub = $this->createRequestStub(['error' => $paramValue]);

        $sut = new OAuthRequest($requestStub);

        $this->assertSame($expected, $sut->hasError());
    }

    public static function errorDataProvider(): iterable
    {
        yield 'access_denied' => ['access_denied', true];
        yield 'invalid_scope' => ['invalid_scope', true];
        yield 'null means no error' => [null, false];
        yield 'empty string means no error' => ['', false];
    }

    private function createRequestStub(array $params): ShopRequest
    {
        $requestStub = $this->createPartialMock(ShopRequest::class, ['getRequestParameter']);
        $requestStub->method('getRequestParameter')
            ->willReturnCallback(fn(string $name) => $params[$name] ?? null);

        return $requestStub;
    }
}
