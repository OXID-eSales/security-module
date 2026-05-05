<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TwoFAException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TwoFactorRequiredException;
use PHPUnit\Framework\TestCase;

class TwoFactorRequiredExceptionTest extends TestCase
{
    public function testException(): void
    {
        $userId = uniqid();
        $verificationUrl = 'https://shop.example/' . uniqid();

        $exception = new TwoFactorRequiredException($userId, $verificationUrl);

        $this->assertInstanceOf(TwoFAException::class, $exception);
        $this->assertSame('ERROR_TWO_FACTOR_REQUIRED', $exception->getMessage());
        $this->assertSame($userId, $exception->getUserId());
        $this->assertSame($verificationUrl, $exception->getVerificationUrl());
    }
}
