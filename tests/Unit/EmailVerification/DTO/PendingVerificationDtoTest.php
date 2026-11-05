<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\DTO;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PendingVerificationDtoTest extends TestCase
{
    #[Test]
    public function getUserId(): void
    {
        $sut = new PendingVerificationDto(
            userId: $userId = uniqid(),
            expiresAt: new DateTimeImmutable(),
            sendCount: rand(),
            lastSentAt: new DateTimeImmutable(),
        );

        $this->assertSame($userId, $sut->getUserId());
    }

    #[Test]
    public function getExpiresAt(): void
    {
        $sut = new PendingVerificationDto(
            userId: uniqid(),
            expiresAt: $expiresAt = new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
            sendCount: rand(),
            lastSentAt: new DateTimeImmutable(),
        );

        $this->assertSame($expiresAt, $sut->getExpiresAt());
    }

    #[Test]
    public function getSendCount(): void
    {
        $sut = new PendingVerificationDto(
            userId: uniqid(),
            expiresAt: new DateTimeImmutable(),
            sendCount: $sendCount = rand(),
            lastSentAt: new DateTimeImmutable(),
        );

        $this->assertSame($sendCount, $sut->getSendCount());
    }

    #[Test]
    public function getLastSentAt(): void
    {
        $sut = new PendingVerificationDto(
            userId: uniqid(),
            expiresAt: new DateTimeImmutable(),
            sendCount: rand(),
            lastSentAt: $lastSentAt = new DateTimeImmutable('-' . rand(1, 60) . ' minutes'),
        );

        $this->assertSame($lastSentAt, $sut->getLastSentAt());
    }
}
