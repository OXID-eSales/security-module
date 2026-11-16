<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\SessionEviction\Service;

use OxidEsales\Eshop\Core\Session;
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
        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')->willReturnMap([
            ['login-token', $sessionValue],
        ]);

        $sut = $this->getSut(session: $sessionStub);

        $this->assertSame($expected, $sut->hasLoginToken());
    }

    public static function hasLoginTokenDataProvider(): array
    {
        return [
            'non-empty token present' => [uniqid('loginToken'), true],
            'empty string token'      => ['', false],
            'null (unset)'            => [null, false],
        ];
    }

    #[Test]
    #[DataProvider('getUserIdDataProvider')]
    public function getUserIdReturnsTheSessionUserIdAsString(string $param, mixed $sessionValue, string $expected): void
    {
        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')->willReturnMap([
            [$param, $sessionValue],
        ]);

        $sut = $this->getSut(session: $sessionStub);

        $this->assertSame($expected, $sut->getUserId($param));
    }

    public static function getUserIdDataProvider(): array
    {
        $userId = uniqid('userId');
        $adminId = uniqid('adminId');

        return [
            'storefront usr set'   => ['usr', $userId, $userId],
            'storefront usr empty' => ['usr', '', ''],
            'storefront usr unset' => ['usr', null, ''],
            'admin auth set'       => ['auth', $adminId, $adminId],
            'admin auth unset'     => ['auth', null, ''],
        ];
    }

    #[Test]
    public function destroySessionDropsTheBasketBeforeStartingANewSession(): void
    {
        $calls = [];
        $shopSessionSpy = $this->createMock(Session::class);
        $shopSessionSpy->expects($this->once())->method('delBasket')
            ->willReturnCallback(function () use (&$calls): void {
                $calls[] = 'delBasket';
            });
        $shopSessionSpy->expects($this->once())->method('initNewSession')
            ->willReturnCallback(function () use (&$calls): void {
                $calls[] = 'initNewSession';
            });

        $sut = $this->getSut(shopSession: $shopSessionSpy);
        $sut->destroySession();

        $this->assertSame(['delBasket', 'initNewSession'], $calls);
    }

    private function getSut(
        ?SessionInterface $session = null,
        ?Session $shopSession = null,
    ): SessionEvictionGuardInterface {
        return new SessionEvictionGuard(
            session: $session ?? $this->createStub(SessionInterface::class),
            shopSession: $shopSession ?? $this->createStub(Session::class),
        );
    }
}
