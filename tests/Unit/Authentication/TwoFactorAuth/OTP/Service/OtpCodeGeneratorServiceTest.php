<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeGeneratorService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeGeneratorServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpCodeGeneratorServiceTest extends TestCase
{
    #[Test]
    public function generateReturnsNumericString(): void
    {
        $sut = $this->getSut();

        $result = $sut->generateCode();

        $this->assertMatchesRegularExpression('/^\d+$/', $result);
    }

    #[Test]
    public function generateReturnsSpecificLengthCode(): void
    {
        $expectedLength = 6;

        $sut = $this->getSut();

        $result = $sut->generateCode();

        $this->assertSame($expectedLength, strlen($result));
    }

    #[Test]
    public function generateReturnsDifferentCodesOnSubsequentCalls(): void
    {
        $sut = $this->getSut();

        $this->assertNotSame($sut->generateCode(), $sut->generateCode());
    }

    private function getSut(): OtpCodeGeneratorServiceInterface
    {
        return new OtpCodeGeneratorService();
    }
}
