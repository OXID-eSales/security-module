<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\OAuth2\Service;

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
        $username = uniqid();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())->method('set')->with('usr');

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($username)->willReturn($username);

        $sut = $this->getSut(
            session: $sessionMock
        );

        $sut->login($oAuth2UserStub);
    }

    public function testLoginWillCreateUser(): void
    {
        $username = uniqid();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())->method('set')->with('usr');

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($username);

        $sut = $this->getSut(
            session: $sessionMock
        );

        $sut->login($oAuth2UserStub);
    }

    public function testLoginWithBlockedUser(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set')->with('usr');

        $username = uniqid();
        $userInfraStub = $this->createStub(UserRepositoryInterface::class);
        $userInfraStub->method('getUserByEmail')
            ->with($username)
            ->willThrowException(new UserBlockedException());

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($username);

        $sut = $this->getSut(
            userRepository: $userInfraStub,
            session: $sessionMock
        );

        $this->expectException(UserBlockedException::class);

        $sut->login($oAuth2UserStub);
    }

    public function testLoginWithNonExistingUser(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set')->with('usr');

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn(null);

        $sut = $this->getSut(
            session: $sessionMock
        );

        $this->expectException(UserNotFoundException::class);

        $sut->login($oAuth2UserStub);
    }

    public function testCannotLoginWithoutEmail(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->never())->method('set')->with('usr');

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);

        $sut = $this->getSut(
            session: $sessionMock
        );

        $this->expectException(UserNotFoundException::class);

        $sut->login($oAuth2UserStub);
    }

    public function testLoginWithExistingEmail(): void
    {
        $username = uniqid();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())->method('set')->with('usr');

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($username);

        $userDTOStub = $this->createConfiguredStub(UserDTOInterface::class, [
            'getId' => uniqid(),
            'isBlocked' => false,
        ]);

        $userInfraStub = $this->createMock(UserRepositoryInterface::class);
        $userInfraStub
            ->method('getUserByEmail')
            ->with($username)
            ->willReturn($userDTOStub);
        $userInfraStub
            ->expects($this->never())
            ->method('createUser');

        $sut = $this->getSut(
            userRepository: $userInfraStub,
            session: $sessionMock
        );

        $sut->login($oAuth2UserStub);
    }

    public function testLoginCreateUserWhenEmailNotFoundInDB(): void
    {
        $username = uniqid();

        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->expects($this->once())->method('set')->with('usr');

        $oAuth2UserStub = $this->createStub(OAuth2UserDTOInterface::class);
        $oAuth2UserStub->method('getEmail')->willReturn($username);

        $userDTOStub = $this->createConfiguredStub(UserDTOInterface::class, [
            'getId' => uniqid(),
            'isBlocked' => false,
        ]);

        $userInfraStub = $this->createMock(UserRepositoryInterface::class);
        $userInfraStub
            ->method('getUserByEmail')
            ->with($username)
            ->willThrowException(new UserNotFoundException());
        $userInfraStub
            ->expects($this->once())
            ->method('createUser')
            ->with($oAuth2UserStub)
            ->willReturn($userDTOStub);

        $sut = $this->getSut(
            userRepository: $userInfraStub,
            session: $sessionMock
        );

        $sut->login($oAuth2UserStub);
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
