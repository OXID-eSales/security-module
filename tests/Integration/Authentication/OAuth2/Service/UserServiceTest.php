<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\Object2Group;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Exception\UserBlockedException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserService;

class UserServiceTest extends IntegrationTestCase
{
    public function testLoginWithExistingUser(): void
    {
        $username = uniqid();
        $userId = $this->createTestUser($username);

        $userDTOMock = $this->createMock(UserDTOInterface::class);
        $userDTOMock->method('getEmail')->willReturn($username);

        $sut = $this->getSut();

        $sut->login($userDTOMock);

        $this->assertSame(
            $this->get(SessionInterface::class)->get('usr'),
            $userId
        );
    }

    public function testLoginWillCreateUser(): void
    {
        $username = uniqid();
        $this->createTestUser(uniqid());

        $userDTOMock = $this->createMock(UserDTOInterface::class);
        $userDTOMock->method('getEmail')->willReturn($username);

        $sut = $this->getSut();

        $sut->login($userDTOMock);

        $this->assertNotEmpty(
            $this->get(SessionInterface::class)->get('usr'),
        );
    }

    public function testLoginWithBlockedUser(): void
    {
        $username = uniqid();
        $userId = $this->createTestUser($username);
        $this->addUserGroup($userId, 'oxidblocked');

        $userDTOMock = $this->createMock(UserDTOInterface::class);
        $userDTOMock->method('getEmail')->willReturn($username);

        $sut = $this->getSut();

        $this->expectException(UserBlockedException::class);

        $sut->login($userDTOMock);
    }

    public function testLoginWithNonExistingUser(): void
    {
        $username = uniqid();
        $this->createTestUser($username);

        $userDTOMock = $this->createMock(UserDTOInterface::class);

        $sut = $this->getSut();

        $this->expectException(UserNotFoundException::class);

        $sut->login($userDTOMock);
    }

    public function testGetUserByUserEmailIsFound(): void
    {
        $username = uniqid();
        $this->createTestUser($username);

        $sut = $this->getSut();

        $userModel = $sut->getUserByUserEmail($username);

        $this->assertInstanceOf(UserModel::class, $userModel);
        $this->assertSame($userModel->getFieldData('oxusername'), $username);
    }

    public function testGetUserByUserEmailIsNotFound(): void
    {
        $username = uniqid();
        $sut = $this->getSut();

        $this->expectException(UserNotFoundException::class);

        $sut->getUserByUserEmail($username);
    }

    public function testCreateUser(): void
    {
        $firstName = uniqid();
        $lastName = uniqid();
        $email = uniqid();

        $userDTOMock = $this->createMock(UserDTOInterface::class);
        $userDTOMock->method('getFirstName')->willReturn($firstName);
        $userDTOMock->method('getLastName')->willReturn($lastName);
        $userDTOMock->method('getEmail')->willReturn($email);

        $sut = $this->getSut();

        $userModel = $sut->createUser($userDTOMock);

        $this->assertInstanceOf(UserModel::class, $userModel);
        $this->assertSame($firstName, $userModel->getFieldData('oxfname'));
        $this->assertSame($lastName, $userModel->getFieldData('oxlname'));
        $this->assertSame($email, $userModel->getFieldData('oxusername'));
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
        UserFactoryInterface $userFactory = null,
        SessionInterface $session = null,
    ): UserService {
        return new UserService(
            userFactory: $userFactory ?? $this->get(UserFactoryInterface::class),
            session: $session ?? $this->get(SessionInterface::class)
        );
    }
}
