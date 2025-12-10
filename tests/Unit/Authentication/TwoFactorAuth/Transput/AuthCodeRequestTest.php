<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequest;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\AuthCodeRequestInterface;
use PHPUnit\Framework\TestCase;

class AuthCodeRequestTest extends TestCase
{
    public function testOTPCode()
    {
        $code = uniqid();

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('get')->with('auth_code')->willReturn($code);

        $sut = $this->getSut(
            request: $requestMock,
        );

        $this->assertSame($code, $sut->getCode());
    }

    private function getSut(
        RequestInterface $request,
    ): AuthCodeRequestInterface {
        return new AuthCodeRequest(
            request: $request,
        );
    }
}
