<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserBlockedException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testLoginWithExistingUser(): void
    {
        $userId = uniqid();
        $userEmail = uniqid();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('set')->with('usr', $userId);

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($userEmail);

        $userDTOStub = $this->createConfiguredStub(UserDTOInterface::class, [
            'getId' => $userId,
            'isBlocked' => false
        ]);

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->method('getUserByEmail')->with($userEmail)->willReturn($userDTOStub);
        $userRepositoryMock->expects($this->never())->method('createUser');

        $sut = $this->getSut(session: $sessionMock, userRepository: $userRepositoryMock);

        $sut->login($oAuth2UserStub);
    }

    public function testLoginWillCreateUser(): void
    {
        $userId = uniqid();
        $userEmail = uniqid();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('set')->with('usr', $userId);

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($userEmail);

        $userDTOStub = $this->createConfiguredStub(UserDTOInterface::class, ['getId' => $userId]);

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->method('getUserByEmail')->with($userEmail)
            ->willThrowException(new UserNotFoundException());
        $userRepositoryMock->method('createUser')->with($oAuth2UserStub)->willReturn($userDTOStub);

        $sut = $this->getSut(
            userRepository: $userRepositoryMock,
            session: $sessionMock
        );

        $sut->login($oAuth2UserStub);
    }

    public function testLoginWithBlockedUser(): void
    {
        $username = uniqid();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set')->with('usr');

        $userDTOStub = $this->createConfiguredStub(UserDTOInterface::class, ['isBlocked' => true]);

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserByEmail')
            ->with($username)
            ->willReturn($userDTOStub);

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($username);

        $sut = $this->getSut(
            userRepository: $userRepositoryStub,
            session: $sessionMock
        );

        $this->expectException(UserBlockedException::class);

        $sut->login($oAuth2UserStub);
    }

    public function testCannotLoginWithoutEmail(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set');

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn(null);

        $sut = $this->getSut(
            session: $sessionMock
        );

        $this->expectException(UserNotFoundException::class);

        $sut->login($oAuth2UserStub);
    }

    public function testRemoveExternalAuthFlag(): void
    {
        $userId = uniqid();

        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')->with('usr')->willReturn($userId);

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->expects($this->once())
            ->method('removeExternalAuthFlag')
            ->with($userId);

        $sut = $this->getSut(
            userRepository: $userRepositoryMock,
            session: $sessionStub
        );

        $sut->removeExternalAuthFlag();
    }

    public function testRemoveExternalAuthFlagSkipsWhenNoUserInSession(): void
    {
        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')->with('usr')->willReturn(null);

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->expects($this->never())->method('removeExternalAuthFlag');

        $sut = $this->getSut(
            userRepository: $userRepositoryMock,
            session: $sessionStub
        );

        $sut->removeExternalAuthFlag();
    }

    private function getSut(
        UserRepositoryInterface $userRepository = null,
        SessionInterface $session = null,
    ): UserService {
        return new UserService(
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class),
            session: $session ?? $this->createStub(SessionInterface::class)
        );
    }
}
