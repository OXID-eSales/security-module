<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\Object2Group;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserBlockedException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserService;

class UserServiceTest extends IntegrationTestCase
{
    public function testLoginWithExistingUser(): void
    {
        $username = uniqid();
        $userId = $this->createTestUser($username);

        $userDataObjectMock = $this->createMock(UserInterface::class);
        $userDataObjectMock->method('getEmail')->willReturn($username);

        $sut = $this->getSut();

        $sut->login($userDataObjectMock);

        $this->assertSame(
            $this->get(SessionInterface::class)->get('usr'),
            $userId
        );
    }

    public function testLoginWillCreateUser(): void
    {
        $username = uniqid();
        $this->createTestUser(uniqid());

        $userDataObjectMock = $this->createMock(UserInterface::class);
        $userDataObjectMock->method('getEmail')->willReturn($username);

        $sut = $this->getSut();

        $sut->login($userDataObjectMock);

        $this->assertNotEmpty(
            $this->get(SessionInterface::class)->get('usr'),
        );
    }

    public function testLoginWithBlockedUser(): void
    {
        $username = uniqid();
        $userId = $this->createTestUser($username);
        $this->addUserGroup($userId, 'oxidblocked');

        $userDataObjectMock = $this->createMock(UserInterface::class);
        $userDataObjectMock->method('getEmail')->willReturn($username);

        $sut = $this->getSut();

        $this->expectException(UserBlockedException::class);

        $sut->login($userDataObjectMock);
    }

    public function testLoginWithNonExistingUser(): void
    {
        $username = uniqid();
        $this->createTestUser($username);

        $userDataObjectMock = $this->createMock(UserInterface::class);

        $sut = $this->getSut();

        $this->expectException(UserNotFoundException::class);

        $sut->login($userDataObjectMock);
    }

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

    private function createTestUser(string $username): string
    {
        $user = oxNew(UserModel::class);
        $user->assign([
            'oxactive' => 1,
            'oxshopid' => 1,
            'oxusername' => $username,
        ]);
        $user->save();

        return $user->getId();
    }

    private function addUserGroup(string $userId, string $groupId): void
    {
        $group = oxNew(Object2Group::class);
        $group->init('oxobject2group');
        $group->assign([
            'oxshopid' => 1,
            'oxobjectid' => $userId,
            'oxgroupsid' => $groupId,
        ]);
        $group->save();
    }

    private function getSut(
        UserRepositoryInterface $userRepository = null,
        SessionInterface $session = null,
    ): UserService {
        return new UserService(
            userRepository: $userRepository ?? $this->get(UserRepositoryInterface::class),
            session: $session ?? $this->get(SessionInterface::class)
        );
    }
}
