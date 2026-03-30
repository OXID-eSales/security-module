<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\DTO;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeState;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;
use PHPUnit\Framework\TestCase;

class OtpChallengeStateTest extends TestCase
{
    public function testInitializeAndReadProperties(): void
    {
        $sut = new OtpChallengeState(
            userId: $userId = uniqid(),
            codeHash: $codeHash = uniqid(),
            attempts: $attempts = rand(),
            lastSentAt: $lastSentAt = new \DateTimeImmutable(),
            expiresAt: $expiresAt = new \DateTimeImmutable(),
            verifiedAt: $verifiedAt = new \DateTimeImmutable(),
        );

        $this->assertInstanceOf(OtpChallengeStateInterface::class, $sut);
        $this->assertSame($userId, $sut->getUserId());
        $this->assertSame($codeHash, $sut->getCodeHash());
        $this->assertSame($attempts, $sut->getAttempts());
        $this->assertSame($lastSentAt, $sut->getLastSentAt());
        $this->assertSame($expiresAt, $sut->getExpiresAt());
        $this->assertSame($verifiedAt, $sut->getVerifiedAt());
    }

    public function testLastSentAtAndVerifiedAtAreNullable(): void
    {
        $sut = new OtpChallengeState(
            userId: uniqid(),
            codeHash: uniqid(),
            attempts: 0,
            lastSentAt: null,
            expiresAt: new \DateTimeImmutable(),
            verifiedAt: null,
        );

        $this->assertNull($sut->getLastSentAt());
        $this->assertNull($sut->getVerifiedAt());
    }
}
