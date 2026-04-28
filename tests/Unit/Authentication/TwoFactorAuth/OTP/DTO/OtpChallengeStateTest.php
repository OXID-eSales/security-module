<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\DTO;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeState;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpChallengeStateTest extends TestCase
{
    #[Test]
    public function initializeAndReadProperties(): void
    {
        $sut = $this->getSut(
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

    #[Test]
    public function lastSentAtAndVerifiedAtAreNullable(): void
    {
        $sut = $this->getSut(lastSentAt: null, verifiedAt: null);

        $this->assertNull($sut->getLastSentAt());
        $this->assertNull($sut->getVerifiedAt());
    }

    private function getSut(
        string $userId = 'user_id',
        string $codeHash = 'code_hash',
        int $attempts = 0,
        ?\DateTimeImmutable $lastSentAt = null,
        ?\DateTimeImmutable $expiresAt = null,
        ?\DateTimeImmutable $verifiedAt = null,
    ): OtpChallengeState {
        return new OtpChallengeState(
            userId: $userId,
            codeHash: $codeHash,
            attempts: $attempts,
            lastSentAt: $lastSentAt,
            expiresAt: $expiresAt ?? new \DateTimeImmutable(),
            verifiedAt: $verifiedAt,
        );
    }
}
