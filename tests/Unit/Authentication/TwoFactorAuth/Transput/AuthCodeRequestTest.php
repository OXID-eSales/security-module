<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequest;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
use PHPUnit\Framework\TestCase;

class AuthCodeRequestTest extends TestCase
{
    public function testGetCodeReturnsValidCode(): void
    {
        $code = uniqid();

        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('get')->with('auth_code')->willReturn($code);

        $sut = $this->getSut(
            request: $requestStub,
        );

        $this->assertSame($code, $sut->getCode());
    }

    public function testGetCodeThrowsExceptionWhenCodeIsEmpty(): void
    {
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('get')->with('auth_code')->willReturn('');

        $sut = $this->getSut(
            request: $requestStub,
        );

        $this->expectException(InvalidCodeException::class);

        $sut->getCode();
    }

    public function testGetCodeThrowsExceptionWhenCodeIsNull(): void
    {
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('get')->with('auth_code')->willReturn(null);

        $sut = $this->getSut(
            request: $requestStub,
        );

        $this->expectException(InvalidCodeException::class);

        $sut->getCode();
    }

    public function testGetCodeThrowsExceptionWhenCodeIsArray(): void
    {
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('get')->with('auth_code')->willReturn(['code']);

        $sut = $this->getSut(
            request: $requestStub,
        );

        $this->expectException(InvalidCodeException::class);

        $sut->getCode();
    }

    public function testGetCodeThrowsExceptionWhenCodeIsInteger(): void
    {
        $requestStub = $this->createStub(RequestInterface::class);
        $requestStub->method('get')->with('auth_code')->willReturn(123456);

        $sut = $this->getSut(
            request: $requestStub,
        );

        $this->expectException(InvalidCodeException::class);

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
