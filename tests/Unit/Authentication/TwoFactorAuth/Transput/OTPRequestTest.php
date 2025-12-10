<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\OTPRequest;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput\OTPRequestInterface;
use PHPUnit\Framework\TestCase;

class OTPRequestTest extends TestCase
{
    public function testOTPCode()
    {
        $code = uniqid();

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('get')->with('auth_code')->willReturn($code);

        $sut = $this->getSut(
            request: $requestMock,
        );

        $this->assertSame($code, $sut->getOTPCode());
    }

    private function getSut(
        RequestInterface $request,
    ): OTPRequestInterface {
        return new OTPRequest(
            request: $request,
        );
    }
}
