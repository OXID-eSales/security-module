<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure;

use Doctrine\DBAL\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {
    }

    public function getUserByEmail(string $username): string|bool
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder
            ->select('u.oxid')
            ->from('oxuser', 'u')
            ->where('u.oxusername = :oxusername')
            ->setParameter('oxusername', $username);

        /** @var Result $result */
        $result = $queryBuilder->execute();

        return $result->fetchOne();
    }
}
