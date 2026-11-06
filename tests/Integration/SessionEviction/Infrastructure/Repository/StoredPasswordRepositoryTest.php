<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction\Infrastructure\Repository;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\SessionEviction\Infrastructure\Repository\StoredPasswordRepository;
use OxidEsales\SecurityModule\SessionEviction\Infrastructure\Repository\StoredPasswordRepositoryInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class StoredPasswordRepositoryTest extends IntegrationTestCase
{
    private const USER_ID = 'testuser';

    #[Test]
    public function getPasswordHashReturnsTheStoredHashForAnExistingUser(): void
    {
        $user = oxNew(User::class);
        $user->load(self::USER_ID);
        $expected = (string) $user->getFieldData('oxpassword');

        $this->assertNotSame('', $expected);
        $this->assertSame($expected, $this->getSut()->getPasswordHash(self::USER_ID));
    }

    #[Test]
    public function getPasswordHashReturnsEmptyStringForAnUnknownUser(): void
    {
        $this->assertSame('', $this->getSut()->getPasswordHash(uniqid('missing-', true)));
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null
    ): StoredPasswordRepositoryInterface {
        return new StoredPasswordRepository(
            queryBuilderFactory: $queryBuilderFactory ?? $this->get(QueryBuilderFactoryInterface::class),
        );
    }
}
