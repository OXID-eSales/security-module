<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\MalformedRequestException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequest;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AuthCodeRequestTest extends TestCase
{
    public static function validCodeDataProvider(): \Generator
    {
        $code = uniqid();
        yield 'string code' => ['input' => $code, 'expected' => $code];
        yield 'empty string' => ['input' => '', 'expected' => ''];
        yield 'integer code' => ['input' => 123456, 'expected' => '123456'];
    }

    #[DataProvider('validCodeDataProvider')]
    public function testGetCodeReturnsCode(mixed $input, string $expected): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('get')->with('auth_code')->willReturn($input);

        $sut = $this->getSut(request: $requestMock);

        $this->assertSame($expected, $sut->getCode());
    }

    public static function invalidCodeDataProvider(): \Generator
    {
        yield 'null' => [null];
        yield 'array' => [['code']];
    }

    #[DataProvider('invalidCodeDataProvider')]
    public function testGetCodeThrowsExceptionForInvalidInput(mixed $value): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('get')->with('auth_code')->willReturn($value);

        $sut = $this->getSut(request: $requestMock);

        $this->expectException(MalformedRequestException::class);

        $sut->getCode();
    }

    private function getSut(
        RequestInterface $request,
    ): AuthCodeRequestInterface {
        return new AuthCodeRequest(
            request: $request,
        );
    }
}
