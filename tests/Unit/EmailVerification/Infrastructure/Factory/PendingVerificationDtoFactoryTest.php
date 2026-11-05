<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Infrastructure\Factory;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Factory\PendingVerificationDtoFactory;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Factory\PendingVerificationDtoFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PendingVerificationDtoFactoryTest extends TestCase
{
    #[Test]
    public function createFromDatabaseRow(): void
    {
        $expiresAt = new DateTimeImmutable('+' . rand(1, 48) . ' hours');
        $lastSentAt = new DateTimeImmutable('-' . rand(1, 60) . ' minutes');

        $sut = $this->getSut();
        $result = $sut->createFromDatabaseRow([
            'OXUSERID' => $userId = uniqid(),
            'EXPIRES_AT' => $expiresAt->format('Y-m-d H:i:s'),
            'SEND_COUNT' => (string) ($sendCount = rand(1, 10)),
            'LAST_SENT_AT' => $lastSentAt->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($expiresAt->getTimestamp(), $result->getExpiresAt()->getTimestamp());
        $this->assertSame($sendCount, $result->getSendCount());
        $this->assertSame($lastSentAt->getTimestamp(), $result->getLastSentAt()->getTimestamp());
    }

    private function getSut(): PendingVerificationDtoFactoryInterface
    {
        return new PendingVerificationDtoFactory();
    }
}
