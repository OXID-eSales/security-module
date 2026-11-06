<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\SessionEviction\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\SessionEviction\Infrastructure\Repository\StoredPasswordRepositoryInterface;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionGuardInterface;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionService;
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
        $guard->method('hasUserId')->willReturn(true);
        $guard->method('hasLoginToken')->willReturn(false);
        $guard->expects($this->once())->method('forgetUserId')->with('usr');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(isAdmin: false), false);
    }

    #[Test]
    public function evictUnverifiableSessionUsesAdminParameterWhenForced(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('hasUserId')->willReturn(true);
        $guard->method('hasLoginToken')->willReturn(false);
        $guard->expects($this->once())->method('forgetUserId')->with('auth');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(isAdmin: false), true);
    }

    #[Test]
    public function evictUnverifiableSessionKeepsSessionThatCarriesAToken(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('hasUserId')->willReturn(true);
        $guard->method('hasLoginToken')->willReturn(true);
        $guard->expects($this->never())->method('forgetUserId');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(), false);
    }

    #[Test]
    public function evictUnverifiableSessionDoesNothingWhenNoSessionUserIsPresent(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('hasUserId')->willReturn(false);
        $guard->expects($this->never())->method('forgetUserId');

        $this->getSut(settings: $this->settingsStub(enabled: true, strict: true), guard: $guard)
            ->evictUnverifiableSession($this->userStub(), false);
    }

    #[Test]
    public function evictUnverifiableSessionUsesAdminParameterForAnAdminUser(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->method('hasUserId')->willReturn(true);
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
    public function readCurrentPasswordHashReturnsEmptyWhenDisabled(): void
    {
        $repository = $this->createMock(StoredPasswordRepositoryInterface::class);
        $repository->expects($this->never())->method('getPasswordHash');

        $this->assertSame(
            '',
            $this->getSut(settings: $this->settingsStub(enabled: false), repository: $repository)
                ->readCurrentPasswordHash($this->userStub(id: 'user-1'))
        );
    }

    #[Test]
    public function readCurrentPasswordHashReturnsEmptyForUnsavedUser(): void
    {
        $repository = $this->createMock(StoredPasswordRepositoryInterface::class);
        $repository->expects($this->never())->method('getPasswordHash');

        $this->assertSame(
            '',
            $this->getSut(settings: $this->settingsStub(enabled: true), repository: $repository)
                ->readCurrentPasswordHash($this->userStub(id: ''))
        );
    }

    #[Test]
    public function readCurrentPasswordHashReadsStoredHashFromRepository(): void
    {
        $storedHash = uniqid('hash-', true);
        $repository = $this->createMock(StoredPasswordRepositoryInterface::class);
        $repository->expects($this->once())->method('getPasswordHash')->with('user-1')->willReturn($storedHash);

        $this->assertSame(
            $storedHash,
            $this->getSut(settings: $this->settingsStub(enabled: true), repository: $repository)
                ->readCurrentPasswordHash($this->userStub(id: 'user-1'))
        );
    }

    #[Test]
    public function refreshTokenDoesNothingWhenDisabled(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: false))
            ->refreshTokenAfterPasswordChange($user, uniqid('hash-', true));
    }

    #[Test]
    public function refreshTokenDoesNothingWithoutAPriorHash(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->expects($this->never())->method('isActingSessionUser');
        $user = $this->createMock(User::class);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: true), guard: $guard)
            ->refreshTokenAfterPasswordChange($user, '');
    }

    #[Test]
    public function refreshTokenDoesNothingWhenPasswordUnchanged(): void
    {
        $hash = uniqid('hash-', true);
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->expects($this->never())->method('isActingSessionUser');
        $user = $this->createMock(User::class);
        $user->method('getFieldData')->willReturn($hash);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: true), guard: $guard)
            ->refreshTokenAfterPasswordChange($user, $hash);
    }

    #[Test]
    public function refreshTokenDoesNothingWhenTheNewHashIsEmpty(): void
    {
        $guard = $this->createMock(SessionEvictionGuardInterface::class);
        $guard->expects($this->never())->method('isActingSessionUser');
        $user = $this->createMock(User::class);
        $user->method('getFieldData')->willReturn('');
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(settings: $this->settingsStub(enabled: true), guard: $guard)
            ->refreshTokenAfterPasswordChange($user, uniqid('old-', true));
    }

    #[Test]
    public function refreshTokenRefreshesTokenWhenPasswordChangedForActingSession(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getFieldData')->willReturn(uniqid('new-', true));
        $user->method('getId')->willReturn('user-1');
        $user->method('isAdmin')->willReturn(false);
        $user->expects($this->once())->method('setSessionLoginToken');

        $this->getSut(
            settings: $this->settingsStub(enabled: true),
            guard: $this->guardStub(isActingSessionUser: true),
        )->refreshTokenAfterPasswordChange($user, uniqid('old-', true));
    }

    #[Test]
    public function refreshTokenDoesNotRefreshWhenAnotherSessionOwnsTheUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getFieldData')->willReturn(uniqid('new-', true));
        $user->method('getId')->willReturn('user-1');
        $user->method('isAdmin')->willReturn(false);
        $user->expects($this->never())->method('setSessionLoginToken');

        $this->getSut(
            settings: $this->settingsStub(enabled: true),
            guard: $this->guardStub(isActingSessionUser: false),
        )->refreshTokenAfterPasswordChange($user, uniqid('old-', true));
    }

    private function getSut(
        ?ModuleSettingsServiceInterface $settings = null,
        ?SessionEvictionGuardInterface $guard = null,
        ?StoredPasswordRepositoryInterface $repository = null,
    ): SessionEvictionService {
        return new SessionEvictionService(
            $settings ?? $this->settingsStub(enabled: true),
            $guard ?? $this->createStub(SessionEvictionGuardInterface::class),
            $repository ?? $this->createStub(StoredPasswordRepositoryInterface::class),
        );
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
        bool $hasUserId = false,
        bool $isActingSessionUser = false,
    ): SessionEvictionGuardInterface {
        return $this->createConfiguredStub(
            SessionEvictionGuardInterface::class,
            [
                'hasLoginToken' => $hasLoginToken,
                'hasUserId' => $hasUserId,
                'isActingSessionUser' => $isActingSessionUser,
            ]
        );
    }

    private function userStub(bool $isAdmin = false, string $id = ''): User
    {
        return $this->createConfiguredStub(
            User::class,
            ['isAdmin' => $isAdmin, 'getId' => $id]
        );
    }
}
