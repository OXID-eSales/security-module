<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\EmailVerification\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Factory\PendingVerificationDtoFactoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepository;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

class PendingVerificationRepositoryTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->cleanupTable();
    }

    #[Test]
    public function createPendingVerification(): void
    {
        $sut = $this->getSut();

        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: uniqid(),
            expiresAt: $expiresAt = new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $result = $sut->findByUserId($userId);
        $this->assertSame($expiresAt->getTimestamp(), $result->getExpiresAt()->getTimestamp());
        $this->assertSame(1, $result->getSendCount());
        $this->assertGreaterThanOrEqual(new DateTimeImmutable('-2 seconds'), $result->getLastSentAt());
    }

    #[Test]
    public function createPendingVerificationStoresOnlyTheHashOfTheToken(): void
    {
        $sut = $this->getSut();

        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: $token = uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('TOKEN_HASH')
            ->from('oesm_email_verification')
            ->where('OXUSERID = :userId')
            ->setParameter('userId', $userId);

        $this->assertSame(hash('sha256', $token), $queryBuilder->execute()->fetchOne());
    }

    #[Test]
    public function createPendingVerificationReplacesAnOpenVerificationOfTheUser(): void
    {
        $sut = $this->getSut();
        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: $oldToken = uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );
        $sut->refreshPendingVerification(
            userId: $userId,
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $sut->createPendingVerification(
            userId: $userId,
            token: $newToken = uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $this->assertNull($sut->findByToken($oldToken));
        $this->assertSame(1, $sut->findByToken($newToken)->getSendCount());
    }

    #[Test]
    public function findByUserId(): void
    {
        $sut = $this->getSut();
        $sut->createPendingVerification(
            userId: uniqid(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );
        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $this->assertSame($userId, $sut->findByUserId($userId)->getUserId());
    }

    #[Test]
    public function findByUserIdReturnsNullWhenNotFound(): void
    {
        $sut = $this->getSut();

        $this->assertNull($sut->findByUserId(uniqid()));
    }

    #[Test]
    public function findByToken(): void
    {
        $sut = $this->getSut();
        $sut->createPendingVerification(
            userId: uniqid(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );
        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: $token = uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $this->assertSame($userId, $sut->findByToken($token)->getUserId());
    }

    #[Test]
    public function findByTokenReturnsNullWhenNotFound(): void
    {
        $sut = $this->getSut();

        $this->assertNull($sut->findByToken(uniqid()));
    }

    #[Test]
    public function refreshPendingVerification(): void
    {
        $sut = $this->getSut();
        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $sut->refreshPendingVerification(
            userId: $userId,
            token: $newToken = uniqid(),
            expiresAt: $newExpiresAt = new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $result = $sut->findByToken($newToken);
        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($newExpiresAt->getTimestamp(), $result->getExpiresAt()->getTimestamp());
        $this->assertSame(2, $result->getSendCount());
        $this->assertGreaterThanOrEqual(new DateTimeImmutable('-2 seconds'), $result->getLastSentAt());
    }

    #[Test]
    public function refreshPendingVerificationInvalidatesThePreviousToken(): void
    {
        $sut = $this->getSut();
        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: $oldToken = uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $sut->refreshPendingVerification(
            userId: $userId,
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $this->assertNull($sut->findByToken($oldToken));
    }

    #[Test]
    public function refreshPendingVerificationDoesNotCreateAMissingVerification(): void
    {
        $sut = $this->getSut();

        $sut->refreshPendingVerification(
            userId: $userId = uniqid(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $this->assertNull($sut->findByUserId($userId));
    }

    #[Test]
    public function deletePendingVerification(): void
    {
        $sut = $this->getSut();
        $sut->createPendingVerification(
            userId: $userId = uniqid(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );
        $sut->createPendingVerification(
            userId: $otherUserId = uniqid(),
            token: uniqid(),
            expiresAt: new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        );

        $sut->deletePendingVerification($userId);

        $this->assertNull($sut->findByUserId($userId));
        $this->assertNotNull($sut->findByUserId($otherUserId));
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null,
        ?PendingVerificationDtoFactoryInterface $dtoFactory = null,
    ): PendingVerificationRepositoryInterface {
        return new PendingVerificationRepository(
            queryBuilderFactory: $queryBuilderFactory ?? $this->get(QueryBuilderFactoryInterface::class),
            dtoFactory: $dtoFactory ?? $this->get(PendingVerificationDtoFactoryInterface::class),
        );
    }

    private function cleanupTable(): void
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->delete('oesm_email_verification');
        $queryBuilder->execute();
    }
}
