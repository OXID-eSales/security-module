<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeState;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;

class OtpChallengeStateRepository implements OtpChallengeStateRepositoryInterface
{
    private const TABLE = 'oesm_2fa_otp';
    private const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {
    }

    public function findByUserId(string $userId): ?OtpChallengeStateInterface
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->select('*')
            ->from(self::TABLE)
            ->where('OXUSERID = :userId')
            ->setParameter('userId', $userId);

        /** @var \Doctrine\DBAL\Result $result */
        $result = $builder->execute();
        $row = $result->fetchAssociative();

        if (!$row) {
            return null;
        }

        return new OtpChallengeState(
            userId: $row['OXUSERID'],
            codeHash: $row['CODE_HASH'],
            attempts: (int)$row['ATTEMPTS'],
            lastSentAt: $row['LAST_SENT_AT'] ? new DateTimeImmutable($row['LAST_SENT_AT']) : null,
            expiresAt: new DateTimeImmutable($row['EXPIRES_AT']),
            verifiedAt: $row['VERIFIED_AT'] ? new DateTimeImmutable($row['VERIFIED_AT']) : null,
        );
    }

    public function createChallengeState(string $userId, string $codeHash, DateTimeImmutable $expiresAt): void
    {
        $this->deleteByUserId($userId);

        $builder = $this->queryBuilderFactory->create();
        $builder->insert(self::TABLE)
            ->values([
                'OXUSERID'     => ':userId',
                'CODE_HASH'    => ':codeHash',
                'ATTEMPTS'     => '0',
                'LAST_SENT_AT' => ':lastSentAt',
                'EXPIRES_AT'   => ':expiresAt',
                'VERIFIED_AT'  => 'NULL',
            ])
            ->setParameters([
                'userId'     => $userId,
                'codeHash'   => $codeHash,
                'lastSentAt' => (new DateTimeImmutable())->format(self::DB_DATETIME_FORMAT),
                'expiresAt'  => $expiresAt->format(self::DB_DATETIME_FORMAT),
            ]);

        $builder->execute();
    }

    public function markVerified(string $userId): void
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->update(self::TABLE)
            ->set('VERIFIED_AT', ':verifiedAt')
            ->where('OXUSERID = :userId')
            ->setParameters([
                'userId'     => $userId,
                'verifiedAt' => (new DateTimeImmutable())->format(self::DB_DATETIME_FORMAT),
            ]);

        $builder->execute();
    }

    public function refreshChallengeState(string $userId, string $codeHash, DateTimeImmutable $expiresAt): void
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->update(self::TABLE)
            ->set('CODE_HASH', ':codeHash')
            ->set('LAST_SENT_AT', ':lastSentAt')
            ->set('EXPIRES_AT', ':expiresAt')
            ->where('OXUSERID = :userId')
            ->setParameters([
                'userId'     => $userId,
                'codeHash'   => $codeHash,
                'lastSentAt' => (new DateTimeImmutable())->format(self::DB_DATETIME_FORMAT),
                'expiresAt'  => $expiresAt->format(self::DB_DATETIME_FORMAT),
            ]);

        $builder->execute();
    }

    public function incrementAttempts(string $userId): void
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->update(self::TABLE)
            ->set('ATTEMPTS', 'ATTEMPTS + 1')
            ->where('OXUSERID = :userId')
            ->setParameter('userId', $userId);

        $builder->execute();
    }

    public function deleteChallengeState(string $userId): void
    {
        $this->deleteByUserId($userId);
    }

    private function deleteByUserId(string $userId): void
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->delete(self::TABLE)
            ->where('OXUSERID = :userId')
            ->setParameter('userId', $userId);

        $builder->execute();
    }
}
