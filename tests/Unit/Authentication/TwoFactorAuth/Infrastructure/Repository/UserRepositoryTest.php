<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Infrastructure\Repository;

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepository;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    public function getSut(
        UserFactoryInterface $userFactory = null,
        QueryBuilderFactoryInterface $qbFactory = null,
    ): UserRepositoryInterface {
        return new UserRepository(
            userFactory: $userFactory ?? $this->createMock(UserFactoryInterface::class),
            queryBuilderFactory: $qbFactory ?? $this->createMock(QueryBuilderFactoryInterface::class),
        );
    }

    public function testGetUserOtpDataReturnsDto(): void
    {
        $data = [
            'OXID' => uniqid(),
            'OESMOTPCODE' => uniqid(),
            'OESMOTPATTEMPTS' => random_int(1, 10),
            'OESMOTPEXPTIME' => '2026-01-01T10:00:00',
        ];

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $qbFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $qbFactory->expects($this->once())
            ->method('create')
            ->willReturn($qb);

        $result = $this->createMock(Result::class);
        $qb->expects($this->once())
            ->method('execute')
            ->willReturn($result);

        $result->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn($data);

        $repository = $this->getSut(
            qbFactory: $qbFactory,
        );
        $dto = $repository->getUserOTPData('user');

        $this->assertSame($data['OXID'], $dto->getId());
        $this->assertSame($data['OESMOTPATTEMPTS'], $dto->getAttempts());
        $this->assertSame($data['OESMOTPCODE'], $dto->getCode());
        $this->assertEquals(
            new DateTime($data['OESMOTPEXPTIME']),
            $dto->getExpiresAt()
        );
    }
}
