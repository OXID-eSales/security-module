<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserService;

class UserServiceTest extends IntegrationTestCase
{
    public function testLoginWithoutEmail(): void
    {
        $userDataObjectMock = $this->createMock(UserInterface::class);

        $sut = $this->getSut();

        $this->expectException(UserNotFoundException::class);

        $sut->login($userDataObjectMock);
    }

    public function testGetUserByUserEmailIsFound(): void
    {
        $userDataObjectMock = $this->createMock(UserInterface::class);
        $userDataObjectMock->method('getEmail')->willReturn(uniqid());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->method('getUserByUserEmail')->willReturn(new UserModel());
        $userRepositoryMock->expects($this->never())->method('createUser');

        $sut = $this->getSut(
            userRepository: $userRepositoryMock
        );

        $sut->login($userDataObjectMock);
    }

    public function testGetUserByUserEmailIsNotFoundByCreated(): void
    {
        $userDataObjectMock = $this->createMock(UserInterface::class);
        $userDataObjectMock->method('getEmail')->willReturn(uniqid());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock
            ->method('getUserByUserEmail')
            ->willThrowException(new UserNotFoundException());
        $userRepositoryMock
            ->expects($this->once())
            ->method('createUser')
            ->with($userDataObjectMock)
            ->willReturn(new UserModel());

        $sut = $this->getSut(
            userRepository: $userRepositoryMock
        );

        $sut->login($userDataObjectMock);
    }

    private function getSut(
        UserRepositoryInterface $userRepository = null,
        SessionInterface $session = null,
    ): UserService {
        return new UserService(
            userRepository: $userRepository ?? $this->createMock(UserRepositoryInterface::class),
            session: $session ?? $this->createMock(SessionInterface::class)
        );
    }
}
