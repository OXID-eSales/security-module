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
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SessionEvictionServiceTest extends TestCase
{
    #[Test]
    public function evictUnverifiableSessionDoesNothingWhenDisabled(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->expects($this->never())->method('forgetUserId');

        $this->getSut(settings: $this->settingsStub(enabled: false, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(), false);
    }

    #[Test]
    public function evictUnverifiableSessionDoesNothingWhenNotStrict(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->expects($this->never())->method('forgetUserId');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: false), guard: $guard)
            ->evictUnverifiableSession($this->userStub(), false);
    }

    #[Test]
    public function evictUnverifiableSessionForgetsTokenlessStorefrontSessionUser(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('getUserId')->willReturn(uniqid('user-', true));
        $guard->method('hasLoginToken')->willReturn(false);
        $guard->expects($this->once())->method('forgetUserId')->with('usr');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(isAdmin: false), false);
    }

    #[Test]
    public function evictUnverifiableSessionUsesAdminParameterWhenForced(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('getUserId')->willReturn(uniqid('user-', true));
        $guard->method('hasLoginToken')->willReturn(false);
        $guard->expects($this->once())->method('forgetUserId')->with('auth');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(isAdmin: false), true);
    }

    #[Test]
    public function evictUnverifiableSessionKeepsSessionThatCarriesAToken(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('getUserId')->willReturn(uniqid('user-', true));
        $guard->method('hasLoginToken')->willReturn(true);
        $guard->expects($this->never())->method('forgetUserId');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(), false);
    }

    #[Test]
    public function evictUnverifiableSessionDoesNothingWhenNoSessionUserIsPresent(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('getUserId')->willReturn('');
        $guard->expects($this->never())->method('forgetUserId');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(), false);
    }

    #[Test]
    public function evictUnverifiableSessionUsesAdminParameterForAnAdminUser(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('getUserId')->willReturn(uniqid('user-', true));
        $guard->method('hasLoginToken')->willReturn(false);
        $guard->expects($this->once())->method('forgetUserId')->with('auth');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(isAdmin: true), false);
    }

    #[Test]
    public function bindLoginTokenDoesNothingWhenDisabled(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: false))
            ->bindLoginTokenToSession($user, true);
    }

    #[Test]
    public function bindLoginTokenDoesNothingWhenUserNotLoaded(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: true))
            ->bindLoginTokenToSession($user, false);
    }

    #[Test]
    public function bindLoginTokenDoesNothingWhenSessionAlreadyHasToken(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: true), guard: $this->guardStub(hasLoginToken: true))
            ->bindLoginTokenToSession($user, true);
    }

    #[Test]
    public function bindLoginTokenSetsTokenOnTokenlessLoadedSession(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: true), guard: $this->guardStub(hasLoginToken: false))
            ->bindLoginTokenToSession($user, true);
    }

    #[Test]
    public function bindLoginTokenToSessionUserDoesNothingWhenDisabled(): void
    {
        $factory = $this->createMock(UserModelFactoryInterface::class);
        $factory->expects($this->never())->method('create');

        $this->getSut(
            settings: $this->settingsStub(enabled: false),
            guard: $this->guardStub(userId: uniqid('user-', true)),
            userModelFactory: $factory,
        )->bindLoginTokenToSessionUser();
    }

    #[Test]
    public function bindLoginTokenToSessionUserDoesNothingWithoutSessionUser(): void
    {
        $factory = $this->createMock(UserModelFactoryInterface::class);
        $factory->expects($this->never())->method('create');

        $this->getSut(guard: $this->guardStub(userId: ''), userModelFactory: $factory)
            ->bindLoginTokenToSessionUser();
    }

    #[Test]
    public function bindLoginTokenToSessionUserSetsTokenForTheStorefrontSessionUser(): void
    {
        $userId = uniqid('user-', true);

        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->expects($this->once())->method('getUserId')->with('usr')->willReturn($userId);

        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('load')->with($userId)->willReturn(true);
        $user->expects($this->once())->method('setSessionLoginToken');

        $this->getSut(guard: $guard, userModelFactory: $this->factoryStub($user))
            ->bindLoginTokenToSessionUser();
    }

    #[Test]
    public function bindLoginTokenToSessionUserSetsNoTokenForAnUnknownUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('load')->willReturn(false);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(
            guard: $this->guardStub(userId: uniqid('user-', true)),
            userModelFactory: $this->factoryStub($user),
        )->bindLoginTokenToSessionUser();
    }

    private function getSut(
        ?ModuleSettingsServiceInterface $settings = null,
        ?SessionEvictionGuardInterface $guard = null,
        ?UserModelFactoryInterface $userModelFactory = null,
    ): SessionEvictionService {
        return new SessionEvictionService(
            $settings ?? $this->settingsStub(enabled: true),
            $guard ?? $this->createStub(SessionEvictionGuardInterface::class),
            $userModelFactory ?? $this->createStub(UserModelFactoryInterface::class),
        );
    }

    private function factoryStub(User $user): UserModelFactoryInterface
    {
        return $this->createConfiguredStub(UserModelFactoryInterface::class, ['create' => $user]);
    }

    private function settingsStub(bool $enabled, bool $strict = false): ModuleSettingsServiceInterface
    {
        return $this->createConfiguredStub(
            ModuleSettingsServiceInterface::class,
            ['isEnabled' => $enabled, 'isStrict' => $strict]
        );
    }

    private function guardStub(
        bool $hasLoginToken = false,
        string $userId = '',
    ): SessionEvictionGuardInterface {
        return $this->createConfiguredStub(
            SessionEvictionGuardInterface::class,
            [
                'hasLoginToken' => $hasLoginToken,
                'getUserId' => $userId,
            ]
        );
    }

    private function userStub(bool $isAdmin = false): User
    {
        return $this->createConfiguredStub(
            User::class,
            ['isAdmin' => $isAdmin]
        );
    }
}
