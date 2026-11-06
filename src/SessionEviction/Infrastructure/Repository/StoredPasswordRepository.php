<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Infrastructure\Repository;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class StoredPasswordRepository implements StoredPasswordRepositoryInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
    }

    public function getPasswordHash(string $userId): string
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXPASSWORD')
            ->from('oxuser')
            ->where('OXID = :userId')
            ->setParameter('userId', $userId);

        /** @var \Doctrine\DBAL\Result $result */
        $result = $queryBuilder->execute();

        return (string) $result->fetchOne();
    }
}
