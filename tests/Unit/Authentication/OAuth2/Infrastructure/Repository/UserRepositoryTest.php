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
use OxidEsales\SecurityModule\Shared\Service\PasswordGeneratorServiceInterface;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    public function testGetUserByEmailReturnsUserDTO(): void
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
            ->method('createFromModel')
            ->with($userModel)
            ->willReturn($userDTOStub);

        $repository = $this->getSut(
            userFactory: $userFactory,
            userDTOFactory: $userDTOFactory,
        );

        $this->assertSame($userDTOStub, $repository->getUserByEmail($username));
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

    public function testCreateUser(): void
    {
        $firstName = uniqid();
        $lastName = uniqid();
        $username = uniqid();

        $userDTOMock = $this->createMock(OAuth2UserDTOInterface::class);
        $userDTOMock->method('getFirstName')->willReturn($firstName);
        $userDTOMock->method('getLastName')->willReturn($lastName);
        $userDTOMock->method('getEmail')->willReturn($username);

        $passwordGenerator = $this->createMock(PasswordGeneratorServiceInterface::class);
        $passwordGenerator->method('generatePasswordForOAuthUser')->willReturn($password = uniqid());

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('assign')->with([
            'OXFNAME'    => $firstName,
            'OXLNAME'    => $lastName,
            'OXUSERNAME' => $username,
        ]);
        $userModel->method('setPassword')->with($password);

        $userFactory = $this->createMock(UserFactoryInterface::class);
        $userFactory->method('create')->willReturn($userModel);

        $userDTOStub = $this->createStub(UserDTOInterface::class);

        $userDTOFactory = $this->createStub(UserDTOFactoryInterface::class);
        $userDTOFactory
            ->method('createFromModel')
            ->with($userModel)
            ->willReturn($userDTOStub);

        $repository = $this->getSut(
            userFactory: $userFactory,
            userDTOFactory: $userDTOFactory,
            passwordGenerator: $passwordGenerator
        );

        $this->assertEquals($userDTOStub, $repository->createUser($userDTOMock));
    }

    private function getSut(
        UserFactoryInterface $userFactory = null,
        UserDTOFactoryInterface $userDTOFactory = null,
        PasswordGeneratorServiceInterface $passwordGenerator = null
    ): UserRepository {
        return new UserRepository(
            $userFactory ?? $this->createStub(UserFactoryInterface::class),
            $userDTOFactory ?? $this->createStub(UserDTOFactoryInterface::class),
            $passwordGenerator ?? $this->createStub(PasswordGeneratorServiceInterface::class)
        );
    }
}
