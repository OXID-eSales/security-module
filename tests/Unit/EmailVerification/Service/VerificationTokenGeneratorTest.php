<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use OxidEsales\SecurityModule\EmailVerification\Service\VerificationTokenGenerator;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationTokenGeneratorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VerificationTokenGeneratorTest extends TestCase
{
    #[Test]
    public function generateToken(): void
    {
        $sut = $this->getSut();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $sut->generateToken());
    }

    #[Test]
    public function generateTokenReturnsDifferentTokensOnSubsequentCalls(): void
    {
        $sut = $this->getSut();

        $this->assertNotSame($sut->generateToken(), $sut->generateToken());
    }

    private function getSut(): VerificationTokenGeneratorInterface
    {
        return new VerificationTokenGenerator();
    }
}
