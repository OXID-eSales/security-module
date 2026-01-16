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
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;
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
            userFactory: $userFactory ?? $this->createStub(UserFactoryInterface::class),
            queryBuilderFactory: $qbFactory ?? $this->createStub(QueryBuilderFactoryInterface::class),
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

        $qbSpy = $this->createMock(QueryBuilder::class);
        $qbSpy->method('select')->willReturnSelf();
        $qbSpy->method('from')->willReturnSelf();
        $qbSpy->method('where')->willReturnSelf();
        $qbSpy->method('setParameter')->willReturnSelf();

        $qbFactorySpy = $this->createMock(QueryBuilderFactoryInterface::class);
        $qbFactorySpy->expects($this->once())
            ->method('create')
            ->willReturn($qbSpy);

        $resultSpy = $this->createMock(Result::class);
        $qbSpy->expects($this->once())
            ->method('execute')
            ->willReturn($resultSpy);

        $resultSpy->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn($data);

        $repository = $this->getSut(
            qbFactory: $qbFactorySpy,
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

    public function testGetUserOtpDataThrowsWhenUserNotFound(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->method('select')->willReturnSelf();
        $queryBuilderMock->method('from')->willReturnSelf();
        $queryBuilderMock->method('where')->willReturnSelf();
        $queryBuilderMock->method('setParameter')->willReturnSelf();

        $queryBuilderFactoryMock = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactoryMock->method('create')->willReturn($queryBuilderMock);

        $resultMock = $this->createMock(Result::class);
        $queryBuilderMock->method('execute')->willReturn($resultMock);
        $resultMock->method('fetchAssociative')->willReturn(false);

        $repository = $this->getSut(
            qbFactory: $queryBuilderFactoryMock,
        );

        $this->expectException(UserNotFoundException::class);

        $repository->getUserOTPData('missing-user');
    }

    public function testAddOtpToUserPersistsCorrectData(): void
    {
        $expiresAt = new DateTime('+5 minutes');

        $userModelSpy = $this->createMock(User::class);
        $userModelSpy->expects($this->once())->method('load')->with($userId = uniqid());
        $userModelSpy->expects($this->once())->method('assign')->with([
            'OESMOTPCODE'     => $code = uniqid(),
            'OESMOTPEXPTIME'  => $expiresAt->format('Y-m-d H:i:s'),
            'OESMOTPATTEMPTS' => 0,
        ]);
        $userModelSpy->expects($this->once())->method('save');

        $userFactoryMock = $this->createMock(UserFactoryInterface::class);
        $userFactoryMock->method('create')->willReturn($userModelSpy);

        $repository = $this->getSut(
            userFactory: $userFactoryMock,
        );

        $this->assertTrue(
            $repository->addOTPtoUser($userId, $code, $expiresAt)
        );
    }

    public function testUpdateAttempts(): void
    {
        $userModelSpy = $this->createMock(User::class);
        $userModelSpy->expects($this->once())->method('load')->with($userId = uniqid());
        $userModelSpy->expects($this->once())->method('assign')->with([
            'OESMOTPATTEMPTS' => 3,
        ]);
        $userModelSpy->expects($this->once())->method('save');

        $userFactoryMock = $this->createMock(UserFactoryInterface::class);
        $userFactoryMock->method('create')->willReturn($userModelSpy);

        $repository = $this->getSut(
            userFactory: $userFactoryMock,
        );

        $repository->updateAttempts($userId, 3);
    }

    public function testResetCodeFieldsClearsOtpData(): void
    {
        $userModelSpy = $this->createMock(User::class);
        $userModelSpy->expects($this->once())->method('load')->with($userId = uniqid());
        $userModelSpy->expects($this->once())->method('assign')->with([
            'OESMOTPCODE'     => '',
            'OESMOTPEXPTIME'  => 0,
            'OESMOTPATTEMPTS' => 0,
        ]);
        $userModelSpy->expects($this->once())->method('save');

        $userFactoryMock = $this->createMock(UserFactoryInterface::class);
        $userFactoryMock->method('create')->willReturn($userModelSpy);

        $repository = $this->getSut(
            userFactory: $userFactoryMock,
        );

        $repository->resetCodeFields($userId);
    }

    public function testGetUserPasswordHashReturnsHash(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->method('select')->willReturnSelf();
        $queryBuilderMock->method('from')->willReturnSelf();
        $queryBuilderMock->method('where')->willReturnSelf();
        $queryBuilderMock->method('setParameter')->willReturnSelf();

        $queryBuilderFactoryMock = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactoryMock->method('create')->willReturn($queryBuilderMock);

        $resultStub = $this->createStub(Result::class);
        $queryBuilderMock->method('execute')->willReturn($resultStub);
        $resultStub->method('fetchOne')->willReturn($pwd = uniqid());

        $repository = $this->getSut(
            qbFactory: $queryBuilderFactoryMock,
        );

        $this->assertSame(
            $pwd,
            $repository->getUserPasswordHash('user')
        );
    }

    public function testGetUserPasswordHashReturnsNullWhenEmpty(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->method('select')->willReturnSelf();
        $queryBuilderMock->method('from')->willReturnSelf();
        $queryBuilderMock->method('where')->willReturnSelf();
        $queryBuilderMock->method('setParameter')->willReturnSelf();

        $queryBuilderFactoryMock = $this->createMock(QueryBuilderFactoryInterface::class);
        $queryBuilderFactoryMock->method('create')->willReturn($queryBuilderMock);

        $resultStub = $this->createStub(Result::class);
        $queryBuilderMock->method('execute')->willReturn($resultStub);
        $resultStub->method('fetchOne')->willReturn(false);

        $repository = $this->getSut(
            qbFactory: $queryBuilderFactoryMock,
        );

        $this->assertNull(
            $repository->getUserPasswordHash(uniqid())
        );
    }
}
