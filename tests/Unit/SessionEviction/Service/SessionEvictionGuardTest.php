<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\SessionEviction\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
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
        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturnMap([
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
        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturnMap([
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
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())
            ->method('remove')
            ->with('usr');

        $this->getSut(session: $session)->forgetUserId('usr');
    }

    #[Test]
    public function forgetUserIdNeverTouchesTokenOrOtherVariables(): void
    {
        $deleted = [];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())
            ->method('remove')
            ->willReturnCallback(function (string $name) use (&$deleted): mixed {
                $deleted[] = $name;
                return null;
            });

        $this->getSut(session: $session)->forgetUserId('auth');

        $this->assertSame(['auth'], $deleted);
        $this->assertNotContains('login-token', $deleted);
    }

    private function getSut(
        ?SessionInterface $session = null,
    ): SessionEvictionGuardInterface {
        return new SessionEvictionGuard(
            session: $session ?? $this->createStub(SessionInterface::class),
        );
    }
}
