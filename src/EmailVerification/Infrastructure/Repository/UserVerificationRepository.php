<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class UserVerificationRepository implements UserVerificationRepositoryInterface
{
    private const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {
    }

    public function markEmailVerified(string $userId): void
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->update('oxuser')
            ->set('OEEMAILVERIFIED', ':verifiedAt')
            ->where('OXID = :userId')
            ->setParameters([
                'userId'     => $userId,
                'verifiedAt' => (new DateTimeImmutable())->format(self::DB_DATETIME_FORMAT),
            ]);

        $builder->execute();
    }
}
