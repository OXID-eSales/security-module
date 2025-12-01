<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Infrastructure\Repository;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\UserDTOFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    public function testGetUserByEmailReturnsUserModel(): void
    {
        $username = uniqid();
        $userId = uniqid();

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('getIdByUserName')->with($username)->willReturn($userId);
        $userModel->method('load')->with($userId)->willReturn(true);

        $userFactory = $this->createMock(UserFactoryInterface::class);
        $userFactory->method('create')->willReturn($userModel);

        $userDTOStub = $this->createStub(UserDTOInterface::class);
        $userDTOStub->method('getId')->willReturn($userId);
        $userDTOStub->method('isBlocked')->willReturn(false);

        $userDTOFactory = $this->createMock(UserDTOFactoryInterface::class);
        $userDTOFactory
            ->expects($this->once())
            ->method('createFromModel')
            ->with($userModel)
            ->willReturn($userDTOStub);

        $repository = $this->getSut(
            userFactory: $userFactory,
            userDTOFactory: $userDTOFactory,
        );

        $this->assertEquals(
            $userDTOStub,
            $repository->getUserByEmail($username)
        );
    }

    public function testGetUserByEmailThrowsExceptionIfUserNotFound(): void
    {
        $username = uniqid();

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('getIdByUserName')->with($username)->willReturn(false);

        $userFactory = $this->createMock(UserFactoryInterface::class);
        $userFactory->method('create')->willReturn($userModel);

        $repository = $this->getSut(
            userFactory: $userFactory
        );

        $this->expectException(UserNotFoundException::class);

        $repository->getUserByEmail($username);
    }

    public function testGetUserByEmailThrowsExceptionIfUserCannotBeLoaded(): void
    {
        $username = uniqid();
        $userId = uniqid();

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('getIdByUserName')->with($username)->willReturn($userId);
        $userModel->method('load')->with($userId)->willReturn(false);

        $userFactory = $this->createMock(UserFactoryInterface::class);

        $repository = $this->getSut(
            userFactory: $userFactory
        );

        $this->expectException(UserNotFoundException::class);

        $repository->getUserByEmail($username);
    }

    public function testUserIsCreatedWhenNotFoundIdDB(): void
    {
        $firstName = uniqid();
        $lastName = uniqid();
        $username = uniqid();
        $userId = uniqid();

        $userDTOMock = $this->createMock(OAuth2UserDTOInterface::class);
        $userDTOMock->method('getFirstName')->willReturn($firstName);
        $userDTOMock->method('getLastName')->willReturn($lastName);
        $userDTOMock->method('getEmail')->willReturn($username);

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('getId')->willReturn($userId);
        $userModel->method('inGroup')->with('oxidblocked')->willReturn(false);
        $userModel->expects($this->once())->method('assign');
        $userModel->expects($this->once())->method('setPassword');

        $userFactory = $this->createMock(UserFactoryInterface::class);
        $userFactory->method('create')->willReturn($userModel);

        $userDTOStub = $this->createStub(UserDTOInterface::class);

        $userDTOFactory = $this->createStub(UserDTOFactoryInterface::class);
        $userDTOFactory
            ->expects($this->once())
            ->method('createFromModel')
            ->with($userModel)
            ->willReturn($userDTOStub);

        $repository = $this->getSut(
            userFactory: $userFactory,
            userDTOFactory: $userDTOFactory
        );

        $this->assertEquals(
            $userDTOStub,
            $repository->createUser($userDTOMock)
        );
    }

    private function getSut(
        UserFactoryInterface $userFactory = null,
        UserDTOFactoryInterface $userDTOFactory = null,
    ): UserRepository {
        return new UserRepository(
            $userFactory ?? $this->createStub(UserFactoryInterface::class),
            $userDTOFactory ?? $this->createStub(UserDTOFactoryInterface::class),
        );
    }
}
