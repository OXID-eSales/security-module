<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\SessionEviction\Service;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionGuard;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionGuardInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SessionEvictionGuardTest extends TestCase
{
    #[Test]
    #[DataProvider('hasLoginTokenDataProvider')]
    public function hasLoginTokenReflectsTokenPresence(mixed $sessionValue, bool $expected): void
    {
        $session = $this->createStub(Session::class);
        $session->method('getVariable')->willReturnMap([
            ['login-token', $sessionValue],
        ]);

        $this->assertSame($expected, $this->getSut(session: $session)->hasLoginToken());
    }

    public static function hasLoginTokenDataProvider(): array
    {
        return [
            'non-empty token present' => [uniqid('token-', true), true],
            'empty string token'      => ['', false],
            'null (unset)'            => [null, false],
        ];
    }

    #[Test]
    #[DataProvider('hasUserIdDataProvider')]
    public function hasUserIdReflectsUserVariablePresence(string $param, mixed $sessionValue, bool $expected): void
    {
        $session = $this->createStub(Session::class);
        $session->method('getVariable')->willReturnMap([
            [$param, $sessionValue],
        ]);

        $this->assertSame($expected, $this->getSut(session: $session)->hasUserId($param));
    }

    public static function hasUserIdDataProvider(): array
    {
        return [
            'storefront usr set'   => ['usr', uniqid('user-', true), true],
            'storefront usr empty' => ['usr', '', false],
            'storefront usr unset' => ['usr', null, false],
            'admin auth set'       => ['auth', uniqid('admin-', true), true],
            'admin auth unset'     => ['auth', null, false],
        ];
    }

    #[Test]
    public function forgetUserIdDeletesOnlyTheGivenUserIdVariable(): void
    {
        $session = $this->createMock(Session::class);
        $session->expects($this->once())
            ->method('deleteVariable')
            ->with('usr');

        $this->getSut(session: $session)->forgetUserId('usr');
    }

    #[Test]
    public function forgetUserIdNeverTouchesTokenOrOtherVariables(): void
    {
        $deleted = [];
        $session = $this->createMock(Session::class);
        $session->expects($this->once())
            ->method('deleteVariable')
            ->willReturnCallback(function (string $name) use (&$deleted): void {
                $deleted[] = $name;
            });

        $this->getSut(session: $session)->forgetUserId('auth');

        $this->assertSame(['auth'], $deleted);
        $this->assertNotContains('login-token', $deleted);
    }

    #[Test]
    #[DataProvider('isActingSessionUserDataProvider')]
    public function isActingSessionUserComparesSessionUserWithGivenId(
        string $userId,
        string $param,
        mixed $sessionValue,
        bool $expected
    ): void {
        $session = $this->createStub(Session::class);
        $session->method('getVariable')->willReturnMap([
            [$param, $sessionValue],
        ]);

        $sut = $this->getSut(session: $session);

        $this->assertSame($expected, $sut->isActingSessionUser($userId, $param));
    }

    public static function isActingSessionUserDataProvider(): array
    {
        $userId = uniqid('user-', true);
        $otherUserId = uniqid('other-', true);
        $adminId = uniqid('admin-', true);

        return [
            'session owns this user'        => [$userId, 'usr', $userId, true],
            'session owns a different user' => [$userId, 'usr', $otherUserId, false],
            'session has no user'           => [$userId, 'usr', null, false],
            'admin session owns user'       => [$adminId, 'auth', $adminId, true],
        ];
    }

    private function getSut(
        ?Session $session = null,
    ): SessionEvictionGuardInterface {
        return new SessionEvictionGuard(
            session: $session ?? $this->createStub(Session::class),
        );
    }
}
