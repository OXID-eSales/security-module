<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeHasherService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeHasherServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpCodeHasherServiceTest extends TestCase
{
    #[Test]
    public function hashReturnsDifferentValueThanInput(): void
    {
        $sut = $this->getSut();

        $result = $sut->hash(code: $code = uniqid());

        $this->assertNotSame($code, $result);
    }

    #[Test]
    public function hashReturnsSameResultForSameInput(): void
    {
        $sut = $this->getSut();
        $code = uniqid();

        $this->assertSame($sut->hash(code: $code), $sut->hash(code: $code));
    }

    #[Test]
    public function hashReturnsDifferentResultForDifferentInput(): void
    {
        $sut = $this->getSut();

        $this->assertNotSame($sut->hash(code: uniqid()), $sut->hash(code: uniqid()));
    }

    private function getSut(): OtpCodeHasherServiceInterface
    {
        return new OtpCodeHasherService();
    }
}
