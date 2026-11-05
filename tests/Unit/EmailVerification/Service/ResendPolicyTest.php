<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use DateTimeImmutable;
use Generator;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\ResendPolicy;
use OxidEsales\SecurityModule\EmailVerification\Service\ResendPolicyInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResendPolicyTest extends TestCase
{
    #[Test]
    #[DataProvider('cooldownBySendCountDataProvider')]
    public function getCooldownRemaining(int $sendCount, int $expectedCooldown): void
    {
        $pendingVerificationStub = $this->createConfiguredStub(PendingVerificationDtoInterface::class, [
            'getSendCount' => $sendCount,
            'getLastSentAt' => new DateTimeImmutable(),
        ]);

        $sut = $this->getSut();

        $this->assertEqualsWithDelta($expectedCooldown, $sut->getCooldownRemaining($pendingVerificationStub), 1);
    }

    public static function cooldownBySendCountDataProvider(): Generator
    {
        yield '1 sent: 2nd mail is free' => [1, 0];
        yield '2 sent: 3rd mail is free' => [2, 0];
        yield '3 sent: 4th mail waits 5 min' => [3, 300];
        yield '4 sent: 5th mail waits 15 min' => [4, 900];
        yield '5 sent: 6th mail waits 60 min' => [5, 3600];
        yield 'more sent: stays at 60 min' => [rand(6, 100), 3600];
    }

    #[Test]
    public function getCooldownRemainingCountsDownFromTheLastSend(): void
    {
        $pendingVerificationStub = $this->createConfiguredStub(PendingVerificationDtoInterface::class, [
            'getSendCount' => 3,
            'getLastSentAt' => new DateTimeImmutable('-' . ($elapsed = rand(1, 299)) . ' seconds'),
        ]);

        $sut = $this->getSut();

        $this->assertEqualsWithDelta(300 - $elapsed, $sut->getCooldownRemaining($pendingVerificationStub), 1);
    }

    #[Test]
    public function getCooldownRemainingIsZeroOnceTheCooldownHasPassed(): void
    {
        $pendingVerificationStub = $this->createConfiguredStub(PendingVerificationDtoInterface::class, [
            'getSendCount' => rand(5, 100),
            'getLastSentAt' => new DateTimeImmutable('-' . rand(3601, 86400) . ' seconds'),
        ]);

        $sut = $this->getSut();

        $this->assertSame(0, $sut->getCooldownRemaining($pendingVerificationStub));
    }

    private function getSut(): ResendPolicyInterface
    {
        return new ResendPolicy();
    }
}
