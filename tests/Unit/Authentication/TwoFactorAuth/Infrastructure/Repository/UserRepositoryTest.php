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
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
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
        ContextInterface $context = null,
    ): UserRepositoryInterface {
        return new UserRepository(
            userFactory: $userFactory ?? $this->createStub(UserFactoryInterface::class),
            queryBuilderFactory: $qbFactory ?? $this->createStub(QueryBuilderFactoryInterface::class),
            context: $context ?? $this->createStub(ContextInterface::class),
        );
    }

    public function testGetUserOtpDataReturnsDto(): void
    {
        $data = [
            'OXID' => uniqid(),
            'OXUSERNAME' => uniqid() . '@example.com',
            'OESMOTPCODE' => uniqid(),
            'OESMOTPATTEMPTS' => random_int(1, 10),
            'OESMOTPEXPTIME' => '2026-01-01T10:00:00',
            'OESMOTPLASTSENT' => '2026-01-01T09:00:00',
        ];

        $qbStub = $this->createStub(QueryBuilder::class);
        $qbStub->method('select')->willReturnSelf();
        $qbStub->method('from')->willReturnSelf();
        $qbStub->method('where')->willReturnSelf();
        $qbStub->method('andWhere')->willReturnSelf();
        $qbStub->method('setParameter')->willReturnSelf();

        $qbFactoryStub = $this->createStub(QueryBuilderFactoryInterface::class);
        $qbFactoryStub->method('create')->willReturn($qbStub);

        $resultStub = $this->createStub(Result::class);
        $qbStub->method('execute')->willReturn($resultStub);
        $resultStub->method('fetchAssociative')->willReturn($data);

        $sut = $this->getSut(
            qbFactory: $qbFactoryStub,
        );

        $dto = $sut->getUserOTPData('user');

        $this->assertSame($data['OXID'], $dto->getId());
        $this->assertSame($data['OXUSERNAME'], $dto->getEmail());
        $this->assertSame($data['OESMOTPATTEMPTS'], $dto->getAttempts());
        $this->assertSame($data['OESMOTPCODE'], $dto->getCode());
        $this->assertEquals(
            new DateTime($data['OESMOTPEXPTIME']),
            $dto->getExpiresAt()
        );
        $this->assertEquals(
            new DateTime($data['OESMOTPLASTSENT']),
            $dto->getLastSentAt()
        );
    }

    public function testGetUserOtpDataThrowsWhenUserNotFound(): void
    {
        $qbStub = $this->createStub(QueryBuilder::class);
        $qbStub->method('select')->willReturnSelf();
        $qbStub->method('from')->willReturnSelf();
        $qbStub->method('where')->willReturnSelf();
        $qbStub->method('andWhere')->willReturnSelf();
        $qbStub->method('setParameter')->willReturnSelf();

        $qbFactoryStub = $this->createStub(QueryBuilderFactoryInterface::class);
        $qbFactoryStub->method('create')->willReturn($qbStub);

        $resultStub = $this->createStub(Result::class);
        $qbStub->method('execute')->willReturn($resultStub);
        $resultStub->method('fetchAssociative')->willReturn(false);

        $sut = $this->getSut(
            qbFactory: $qbFactoryStub,
        );

        $this->expectException(UserNotFoundException::class);

        $sut->getUserOTPData('missing-user');
    }

    public function testAddOtpToUserPersistsCorrectData(): void
    {
        $userId = uniqid();
        $code = uniqid();
        $expiresAt = new DateTime('+5 minutes');

        $userModelMock = $this->createMock(User::class);
        $userModelMock->expects($this->once())->method('load')->with($userId);
        $userModelMock->expects($this->once())->method('assign')->with([
            'OESMOTPCODE'     => $code,
            'OESMOTPEXPTIME'  => $expiresAt->format('Y-m-d H:i:s'),
            'OESMOTPATTEMPTS' => 0,
        ]);
        $userModelMock->expects($this->once())->method('save');

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userModelMock);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
        );

        $this->assertTrue($sut->addOTPtoUser($userId, $code, $expiresAt));
    }

    public function testUpdateAttempts(): void
    {
        $userId = uniqid();

        $userModelMock = $this->createMock(User::class);
        $userModelMock->expects($this->once())->method('load')->with($userId);
        $userModelMock->expects($this->once())->method('assign')->with([
            'OESMOTPATTEMPTS' => 3,
        ]);
        $userModelMock->expects($this->once())->method('save');

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userModelMock);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
        );

        $sut->updateAttempts($userId, 3);
    }

    public function testResetCodeFieldsClearsOtpData(): void
    {
        $userId = uniqid();

        $userModelMock = $this->createMock(User::class);
        $userModelMock->expects($this->once())->method('load')->with($userId);
        $userModelMock->expects($this->once())->method('assign')->with([
            'OESMOTPCODE'     => null,
            'OESMOTPEXPTIME'  => null,
            'OESMOTPATTEMPTS' => 0,
            'OESMOTPLASTSENT' => null,
        ]);
        $userModelMock->expects($this->once())->method('save');

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userModelMock);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
        );

        $sut->resetCodeFields($userId);
    }

    public function testMarkOtpAsSent(): void
    {
        $userId = uniqid();

        $userModelMock = $this->createMock(User::class);
        $userModelMock->expects($this->once())->method('load')->with($userId);
        $userModelMock->expects($this->once())->method('assign');
        $userModelMock->expects($this->once())->method('save');

        $userFactoryStub = $this->createStub(UserFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userModelMock);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
        );

        $sut->markOtpAsSent($userId);
    }
}
