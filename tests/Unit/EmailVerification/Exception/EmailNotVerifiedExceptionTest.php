<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Exception;

use OxidEsales\SecurityModule\EmailVerification\Exception\EmailNotVerifiedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmailNotVerifiedExceptionTest extends TestCase
{
    #[Test]
    public function getUserId(): void
    {
        $sut = new EmailNotVerifiedException(userId: $userId = uniqid(), verificationUrl: uniqid());

        $this->assertSame($userId, $sut->getUserId());
    }

    #[Test]
    public function getVerificationUrl(): void
    {
        $sut = new EmailNotVerifiedException(userId: uniqid(), verificationUrl: $verificationUrl = uniqid());

        $this->assertSame($verificationUrl, $sut->getVerificationUrl());
    }
}
