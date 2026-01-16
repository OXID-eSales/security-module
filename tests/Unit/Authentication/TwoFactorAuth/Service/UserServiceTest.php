<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\AuthorizeServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testHandleLoginSetsSessionAndRedirects(): void
    {
        $username = uniqid();
        $url = uniqid();

        $requestSpy = $this->createMock(Request::class);
        $requestSpy->expects($this->once())
            ->method('getRequestUrl')
            ->willReturn($url);

        $authorizeServiceSpy = $this->createMock(AuthorizeServiceInterface::class);
        $authorizeServiceSpy->expects($this->once())
            ->method('generate');
        $authorizeServiceSpy->expects($this->once())
            ->method('getVerificationUrl')
            ->willReturn($url);

        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function (string $key, $value) use ($username, $url) {
                match ($key) {
                    AuthorizeService::USER_SESSION_KEY =>
                    $this->assertSame($username, $value),

                    AuthorizeService::OTP_TARGET_URL =>
                    $this->assertSame($url, $value),

                    default =>
                    $this->fail('Unexpected session key: ' . $key),
                };
            });

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($url);

        $sut = $this->getSut(
            authorizeService: $authorizeServiceSpy,
            session: $sessionSpy,
            request: $requestSpy,
            utils: $utilsSpy,
        );

        $sut->handleLogin($username);
    }

    public function testCheckPasswordReturnsFalseIfUserNotFound(): void
    {
        $username = uniqid();
        $password = uniqid();

        $userRepositorySpy = $this->createMock(UserRepositoryInterface::class);
        $userRepositorySpy->expects($this->once())
            ->method('getUserPasswordHash')
            ->with($username)
            ->willThrowException(new \Exception());

        $userService = $this->getSut(
            userRepository: $userRepositorySpy,
        );
        $this->assertFalse($userService->checkPassword($username, $password));
    }

    public function testCheckPasswordReturnsFalseIfHashIsNull(): void
    {
        $username = uniqid();
        $password = uniqid();

        $userRepositorySpy = $this->createMock(UserRepositoryInterface::class);
        $userRepositorySpy->expects($this->once())
            ->method('getUserPasswordHash')
            ->with($username)
            ->willReturn(null);

        $userService = $this->getSut(
            userRepository: $userRepositorySpy,
        );
        $this->assertFalse($userService->checkPassword($username, $password));
    }

    public function testCheckPasswordReturnsTrueIfPasswordMatches(): void
    {
        $username = uniqid();
        $password = uniqid();
        $hash = uniqid();

        $userRepositorySpy = $this->createMock(UserRepositoryInterface::class);
        $userRepositorySpy->expects($this->once())
            ->method('getUserPasswordHash')
            ->with($username)
            ->willReturn($hash);

        $pwdServiceBridgeSpy = $this->createMock(PasswordServiceBridgeInterface::class);
        $pwdServiceBridgeSpy->expects($this->once())
            ->method('verifyPassword')
            ->with($password, $hash)
            ->willReturn(true);

        $userService = $this->getSut(
            userRepository: $userRepositorySpy,
            pwdServiceBridge: $pwdServiceBridgeSpy,
        );
        $this->assertTrue($userService->checkPassword($username, $password));
    }

    public function testCheckPasswordReturnsFalseIfPasswordDoesNotMatch(): void
    {
        $username = 'user';
        $password = 'pwd';
        $hash = 'hashedpwd';

        $userRepositorySpy = $this->createMock(UserRepositoryInterface::class);
        $userRepositorySpy->expects($this->once())
            ->method('getUserPasswordHash')
            ->with($username)
            ->willReturn($hash);

        $pwdServiceBridgeSpy = $this->createMock(PasswordServiceBridgeInterface::class);
        $pwdServiceBridgeSpy->expects($this->once())
            ->method('verifyPassword')
            ->with($password, $hash)
            ->willReturn(false);

        $userService = $this->getSut(
            userRepository: $userRepositorySpy,
            pwdServiceBridge: $pwdServiceBridgeSpy,
        );
        $this->assertFalse($userService->checkPassword($username, $password));
    }

    public function getSut(
        AuthorizeServiceInterface $authorizeService = null,
        UserRepositoryInterface $userRepository = null,
        PasswordServiceBridgeInterface $pwdServiceBridge = null,
        SessionInterface $session = null,
        Request $request = null,
        Utils $utils = null,
    ): UserServiceInterface {
        return new UserService(
            authorizeService: $authorizeService ?? $this->createMock(AuthorizeServiceInterface::class),
            userRepository: $userRepository ?? $this->createMock(UserRepositoryInterface::class),
            pwdServiceBridge: $pwdServiceBridge ?? $this->createMock(PasswordServiceBridgeInterface::class),
            session: $session ?? $this->createMock(SessionInterface::class),
            request: $request ?? $this->createMock(Request::class),
            utils: $utils ?? $this->createMock(Utils::class),
        );
    }
}
