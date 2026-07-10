<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepository;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

class OtpChallengeStateRepositoryTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->cleanupTable();
    }

    #[Test]
    public function findByUserIdReturnsNullWhenNotFound(): void
    {
        $sut = $this->getSut();

        $result = $sut->findByUserId(uniqid());

        $this->assertNull($result);
    }

    #[Test]
    public function createChallengeStateAndFind(): void
    {
        $sut = $this->getSut();

        $sut->createChallengeState(
            userId: $userId = uniqid(),
            codeHash: $codeHash = uniqid(),
            expiresAt: $expiresAt = new DateTimeImmutable('+5 minutes'),
        );

        $result = $sut->findByUserId($userId);

        $this->assertInstanceOf(OtpChallengeStateInterface::class, $result);
        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($codeHash, $result->getCodeHash());
        $this->assertSame(0, $result->getAttempts());
        $this->assertNotNull($result->getLastSentAt());
        $this->assertSame($expiresAt->format('Y-m-d H:i:s'), $result->getExpiresAt()->format('Y-m-d H:i:s'));
        $this->assertNull($result->getVerifiedAt());
    }

    #[Test]
    public function createChallengeStateOverwritesExistingChallengeState(): void
    {
        $sut = $this->getSut();

        $sut->createChallengeState(
            userId: $userId = uniqid(),
            codeHash: uniqid(),
            expiresAt: new DateTimeImmutable('+5 minutes'),
        );

        $sut->createChallengeState(
            userId: $userId,
            codeHash: $secondHash = uniqid(),
            expiresAt: $secondExpiresAt = new DateTimeImmutable('+10 minutes'),
        );

        $result = $sut->findByUserId($userId);

        $this->assertSame($secondHash, $result->getCodeHash());
        $this->assertSame(
            $secondExpiresAt->format('Y-m-d H:i:s'),
            $result->getExpiresAt()->format('Y-m-d H:i:s')
        );
    }

    #[Test]
    public function markVerifiedSetsVerifiedAt(): void
    {
        $sut = $this->getSut();
        $sut->createChallengeState(
            userId: $userId = uniqid(),
            codeHash: uniqid(),
            expiresAt: new DateTimeImmutable('+5 minutes'),
        );

        $sut->markVerified($userId);

        $result = $sut->findByUserId($userId);
        $this->assertNotNull($result->getVerifiedAt());
    }

    #[Test]
    public function refreshChallengeStateUpdatesCodeHashLastSentAtAndExpiresAt(): void
    {
        $sut = $this->getSut();
        $sut->createChallengeState(
            userId: $userId = uniqid(),
            codeHash: uniqid(),
            expiresAt: new DateTimeImmutable('+5 minutes'),
        );

        $sut->refreshChallengeState(
            userId: $userId,
            codeHash: $newCodeHash = uniqid(),
            expiresAt: $newExpiresAt = new DateTimeImmutable('+10 minutes'),
        );

        $result = $sut->findByUserId($userId);
        $this->assertSame($newCodeHash, $result->getCodeHash());
        $this->assertGreaterThanOrEqual(
            new DateTimeImmutable('-2 seconds'),
            $result->getLastSentAt()
        );
        $this->assertSame($newExpiresAt->format('Y-m-d H:i:s'), $result->getExpiresAt()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function incrementAttempts(): void
    {
        $sut = $this->getSut();
        $sut->createChallengeState(
            userId: $userId = uniqid(),
            codeHash: uniqid(),
            expiresAt: new DateTimeImmutable('+5 minutes'),
        );

        $sut->incrementAttempts($userId);

        $result = $sut->findByUserId($userId);
        $this->assertSame(1, $result->getAttempts());
    }

    #[Test]
    public function deleteChallengeStateRemovesChallenge(): void
    {
        $sut = $this->getSut();
        $sut->createChallengeState(
            userId: $userId = uniqid(),
            codeHash: uniqid(),
            expiresAt: new DateTimeImmutable('+5 minutes'),
        );

        $sut->deleteChallengeState($userId);

        $this->assertNull($sut->findByUserId($userId));
    }

    #[Test]
    public function deleteChallengeStateNonExistentUserDoesNotExplode(): void
    {
        $sut = $this->getSut();

        $sut->deleteChallengeState(uniqid());

        $this->addToAssertionCount(1);
    }

    private function getSut(): OtpChallengeStateRepositoryInterface
    {
        return new OtpChallengeStateRepository(
            queryBuilderFactory: $this->get(QueryBuilderFactoryInterface::class),
        );
    }

    private function cleanupTable(): void
    {
        $this->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection()
            ->executeStatement('DELETE FROM oesm_2fa_otp');
    }
}
