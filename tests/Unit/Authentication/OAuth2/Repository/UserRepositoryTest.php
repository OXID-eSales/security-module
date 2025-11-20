<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Repository;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    public function testGetUserByUserEmailReturnsUserModel(): void
    {
        $username = uniqid();
        $userId = uniqid();

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('getIdByUserName')->with($username)->willReturn($userId);
        $userModel->method('load')->with($userId)->willReturn(true);

        $userFactory = $this->createMock(UserFactoryInterface::class);
        $userFactory->method('create')->willReturn($userModel);

        $repository = new UserRepository($userFactory);

        $this->assertSame(
            $userModel,
            $repository->getUserByUserEmail($username)
        );
    }

    public function testGetUserByUserEmailThrowsExceptionIfUserNotFound(): void
    {
        $username = uniqid();

        $userModel = $this->createMock(UserModel::class);
        $userModel->method('getIdByUserName')->with($username)->willReturn(false);

        $userFactory = $this->createMock(UserFactoryInterface::class);
        $userFactory->method('create')->willReturn($userModel);

        $repository = new UserRepository($userFactory);

        $this->expectException(UserNotFoundException::class);

        $repository->getUserByUserEmail($username);
    }

    public function testUserCreate(): void
    {
        $userDataObjectMock = $this->createMock(UserInterface::class);
        $userDataObjectMock->method('getEmail')->willReturn(uniqid());

        $userModel = $this->createMock(UserModel::class);

        $userFactory = $this->createMock(UserFactoryInterface::class);
        $userFactory->method('create')->willReturn($userModel);

        $repository = new UserRepository($userFactory);

        $this->assertSame(
            $userModel,
            $repository->createUser($userDataObjectMock)
        );
    }
}
