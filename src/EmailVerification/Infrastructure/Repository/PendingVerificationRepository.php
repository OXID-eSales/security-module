<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Factory\PendingVerificationDtoFactoryInterface;

class PendingVerificationRepository implements PendingVerificationRepositoryInterface
{
    private const TABLE = 'oesm_email_verification';
    private const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private PendingVerificationDtoFactoryInterface $dtoFactory,
    ) {
    }

    public function findByUserId(string $userId): ?PendingVerificationDtoInterface
    {
        return $this->findOneBy('OXUSERID', $userId);
    }

    public function findByToken(#[\SensitiveParameter] string $token): ?PendingVerificationDtoInterface
    {
        return $this->findOneBy('TOKEN_HASH', $this->hashToken($token));
    }

    public function createPendingVerification(
        string $userId,
        #[\SensitiveParameter] string $token,
        DateTimeImmutable $expiresAt
    ): void {
        $this->deletePendingVerification($userId);

        $now = (new DateTimeImmutable())->format(self::DB_DATETIME_FORMAT);

        $builder = $this->queryBuilderFactory->create();
        $builder->insert(self::TABLE)
            ->values([
                'OXUSERID'     => ':userId',
                'TOKEN_HASH'   => ':tokenHash',
                'EXPIRES_AT'   => ':expiresAt',
                'SEND_COUNT'   => '1',
                'LAST_SENT_AT' => ':now',
                'CREATED_AT'   => ':now',
            ])
            ->setParameters([
                'userId'    => $userId,
                'tokenHash' => $this->hashToken($token),
                'expiresAt' => $expiresAt->format(self::DB_DATETIME_FORMAT),
                'now'       => $now,
            ]);

        $builder->execute();
    }

    public function refreshPendingVerification(
        string $userId,
        #[\SensitiveParameter] string $token,
        DateTimeImmutable $expiresAt
    ): void {
        $builder = $this->queryBuilderFactory->create();
        $builder->update(self::TABLE)
            ->set('TOKEN_HASH', ':tokenHash')
            ->set('EXPIRES_AT', ':expiresAt')
            ->set('SEND_COUNT', 'SEND_COUNT + 1')
            ->set('LAST_SENT_AT', ':lastSentAt')
            ->where('OXUSERID = :userId')
            ->setParameters([
                'userId'     => $userId,
                'tokenHash'  => $this->hashToken($token),
                'expiresAt'  => $expiresAt->format(self::DB_DATETIME_FORMAT),
                'lastSentAt' => (new DateTimeImmutable())->format(self::DB_DATETIME_FORMAT),
            ]);

        $builder->execute();
    }

    public function deletePendingVerification(string $userId): void
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->delete(self::TABLE)
            ->where('OXUSERID = :userId')
            ->setParameter('userId', $userId);

        $builder->execute();
    }

    private function hashToken(#[\SensitiveParameter] string $token): string
    {
        return hash('sha256', $token);
    }

    private function findOneBy(string $column, string $value): ?PendingVerificationDtoInterface
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->select('OXUSERID', 'EXPIRES_AT', 'SEND_COUNT', 'LAST_SENT_AT')
            ->from(self::TABLE)
            ->where($column . ' = :value')
            ->setParameter('value', $value);

        /** @var \Doctrine\DBAL\Result $result */
        $result = $builder->execute();
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->dtoFactory->createFromDatabaseRow($row);
    }
}
