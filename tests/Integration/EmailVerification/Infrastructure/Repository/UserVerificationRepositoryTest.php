<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\EmailVerification\Infrastructure\Repository;

use DateTimeImmutable;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\UserVerificationRepository;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\UserVerificationRepositoryInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

class UserVerificationRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function markEmailVerified(): void
    {
        $userId = $this->createUser();
        $otherUserId = $this->createUser();

        $sut = $this->getSut();
        $sut->markEmailVerified($userId);

        $this->assertGreaterThanOrEqual(
            new DateTimeImmutable('-2 seconds'),
            new DateTimeImmutable((string) $this->getVerifiedAt($userId))
        );
        $this->assertNull($this->getVerifiedAt($otherUserId));
    }

    private function getSut(
        ?QueryBuilderFactoryInterface $queryBuilderFactory = null,
    ): UserVerificationRepositoryInterface {
        return new UserVerificationRepository(
            queryBuilderFactory: $queryBuilderFactory ?? $this->get(QueryBuilderFactoryInterface::class),
        );
    }

    private function createUser(): string
    {
        $user = oxNew(UserModel::class);
        $user->setId(uniqid());
        $user->assign(['oxusername' => uniqid() . '@example.test']);
        $user->save();

        return $user->getId();
    }

    private function getVerifiedAt(string $userId): ?string
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder->select('OEEMAILVERIFIED')
            ->from('oxuser')
            ->where('OXID = :userId')
            ->setParameter('userId', $userId);

        $value = $queryBuilder->execute()->fetchOne();

        return $value === false ? null : $value;
    }
}
