<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\SessionEviction\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionGuardInterface;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionService;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionServiceInterface;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SessionEvictionServiceTest extends TestCase
{
    #[Test]
    #[DataProvider('evictionInactiveSettingsDataProvider')]
    public function evictUnverifiableSessionDoesNothingUnlessEnabledAndStrict(bool $enabled, bool $strict): void
    {
        $guardSpy = $this->createMock(SessionEvictionGuardInterface::class);
        $guardSpy->method('getUserId')->willReturn(uniqid('userId'));
        $guardSpy->expects($this->never())->method('destroySession');

        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->never())->method('logout');

        $sut = $this->getSut(
            settings: $this->settingsStub(enabled: $enabled, strict: $strict),
            guard: $guardSpy,
        );
        $sut->evictUnverifiableSession($userSpy, false);
    }

    public static function evictionInactiveSettingsDataProvider(): array
    {
        return [
            'disabled' => [false, true],
            'not strict' => [true, false],
        ];
    }

    #[Test]
    #[DataProvider('userIdParameterDataProvider')]
    public function evictUnverifiableSessionLogsOutAndDestroysTokenlessSession(
        bool $isAdmin,
        bool $forceAdmin,
        string $expectedParameter,
    ): void {
        $guardSpy = $this->createMock(SessionEvictionGuardInterface::class);
        $guardSpy->expects($this->once())
            ->method('getUserId')
            ->with($expectedParameter)
            ->willReturn(uniqid('userId'));
        $guardSpy->method('hasLoginToken')->willReturn(false);
        $guardSpy->expects($this->once())->method('destroySession');

        $userSpy = $this->createMock(User::class);
        $userSpy->method('isAdmin')->willReturn($isAdmin);
        $userSpy->expects($this->once())->method('logout');

        $sut = $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guardSpy);
        $sut->evictUnverifiableSession($userSpy, $forceAdmin);
    }

    public static function userIdParameterDataProvider(): array
    {
        return [
            'storefront user' => [false, false, 'usr'],
            'admin forced' => [false, true, 'auth'],
            'admin user' => [true, false, 'auth'],
        ];
    }

    #[Test]
    #[DataProvider('verifiableSessionDataProvider')]
    public function evictUnverifiableSessionKeepsVerifiableSession(string $userId, bool $hasLoginToken): void
    {
        $guardSpy = $this->createMock(SessionEvictionGuardInterface::class);
        $guardSpy->method('getUserId')->willReturn($userId);
        $guardSpy->method('hasLoginToken')->willReturn($hasLoginToken);
        $guardSpy->expects($this->never())->method('destroySession');

        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->never())->method('logout');

        $sut = $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guardSpy);
        $sut->evictUnverifiableSession($userSpy, false);
    }

    public static function verifiableSessionDataProvider(): array
    {
        return [
            'session carries a token' => [uniqid('userId'), true],
            'no user in session' => ['', false],
        ];
    }

    #[Test]
    public function bindLoginTokenToSessionDoesNothingWhenDisabled(): void
    {
        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->never())->method('setSessionLoginToken');

        $sut = $this->getSut(settings: $this->settingsStub(enabled: false));
        $sut->bindLoginTokenToSession($userSpy, true);
    }

    #[Test]
    public function bindLoginTokenToSessionDoesNothingWhenUserNotLoaded(): void
    {
        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->never())->method('setSessionLoginToken');

        $sut = $this->getSut(settings: $this->settingsStub(enabled: true));
        $sut->bindLoginTokenToSession($userSpy, false);
    }

    #[Test]
    #[DataProvider('bindLoginTokenDataProvider')]
    public function bindLoginTokenToSessionSetsTokenOnlyWhenSessionHasNone(
        bool $hasLoginToken,
        int $expectedCalls,
    ): void {
        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->exactly($expectedCalls))->method('setSessionLoginToken');

        $sut = $this->getSut(
            settings: $this->settingsStub(enabled: true),
            guard: $this->guardStub(hasLoginToken: $hasLoginToken),
        );
        $sut->bindLoginTokenToSession($userSpy, true);
    }

    public static function bindLoginTokenDataProvider(): array
    {
        return [
            'session already has a token' => [true, 0],
            'session has no token' => [false, 1],
        ];
    }

    #[Test]
    public function bindLoginTokenToSessionUserDoesNothingWhenDisabled(): void
    {
        $userModelFactorySpy = $this->createMock(UserModelFactoryInterface::class);
        $userModelFactorySpy->expects($this->never())->method('create');

        $sut = $this->getSut(
            settings: $this->settingsStub(enabled: false),
            guard: $this->guardStub(userId: uniqid('userId')),
            userModelFactory: $userModelFactorySpy,
        );
        $sut->bindLoginTokenToSessionUser();
    }

    #[Test]
    public function bindLoginTokenToSessionUserDoesNothingWithoutSessionUser(): void
    {
        $userModelFactorySpy = $this->createMock(UserModelFactoryInterface::class);
        $userModelFactorySpy->expects($this->never())->method('create');

        $sut = $this->getSut(guard: $this->guardStub(userId: ''), userModelFactory: $userModelFactorySpy);
        $sut->bindLoginTokenToSessionUser();
    }

    #[Test]
    public function bindLoginTokenToSessionUserSetsTokenForTheStorefrontSessionUser(): void
    {
        $userId = uniqid('userId');

        $guardMock = $this->createMock(SessionEvictionGuardInterface::class);
        $guardMock->expects($this->once())->method('getUserId')->with('usr')->willReturn($userId);

        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->once())->method('load')->with($userId)->willReturn(true);
        $userSpy->expects($this->once())->method('setSessionLoginToken');

        $sut = $this->getSut(guard: $guardMock, userModelFactory: $this->userModelFactoryStub($userSpy));
        $sut->bindLoginTokenToSessionUser();
    }

    #[Test]
    public function bindLoginTokenToSessionUserSetsNoTokenForAnUnknownUser(): void
    {
        $userSpy = $this->createMock(User::class);
        $userSpy->method('load')->willReturn(false);
        $userSpy->expects($this->never())->method('setSessionLoginToken');

        $sut = $this->getSut(
            guard: $this->guardStub(userId: uniqid('userId')),
            userModelFactory: $this->userModelFactoryStub($userSpy),
        );
        $sut->bindLoginTokenToSessionUser();
    }

    private function getSut(
        ?ModuleSettingsServiceInterface $settings = null,
        ?SessionEvictionGuardInterface $guard = null,
        ?UserModelFactoryInterface $userModelFactory = null,
    ): SessionEvictionServiceInterface {
        return new SessionEvictionService(
            settings: $settings ?? $this->settingsStub(enabled: true),
            guard: $guard ?? $this->createStub(SessionEvictionGuardInterface::class),
            userModelFactory: $userModelFactory ?? $this->createStub(UserModelFactoryInterface::class),
        );
    }

    private function settingsStub(bool $enabled, bool $strict = false): ModuleSettingsServiceInterface
    {
        return $this->createConfiguredStub(
            ModuleSettingsServiceInterface::class,
            ['isEnabled' => $enabled, 'isStrict' => $strict]
        );
    }

    private function guardStub(bool $hasLoginToken = false, string $userId = ''): SessionEvictionGuardInterface
    {
        return $this->createConfiguredStub(
            SessionEvictionGuardInterface::class,
            ['hasLoginToken' => $hasLoginToken, 'getUserId' => $userId]
        );
    }

    private function userModelFactoryStub(User $user): UserModelFactoryInterface
    {
        return $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $user]);
    }
}
