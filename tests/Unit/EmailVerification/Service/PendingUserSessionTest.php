<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\PendingUserSession;
use OxidEsales\SecurityModule\EmailVerification\Service\PendingUserSessionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PendingUserSessionTest extends TestCase
{
    #[Test]
    public function setPendingUserId(): void
    {
        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->once())
            ->method('set')
            ->with(PendingUserSession::SESSION_KEY, $userId = uniqid());

        $sut = $this->getSut(session: $sessionSpy);

        $sut->setPendingUserId($userId);
    }

    #[Test]
    public function getPendingUserId(): void
    {
        $sessionMock = $this->createMock(SessionInterface::class);
        $sessionMock->method('get')
            ->with(PendingUserSession::SESSION_KEY)
            ->willReturn($userId = uniqid());

        $sut = $this->getSut(session: $sessionMock);

        $this->assertSame($userId, $sut->getPendingUserId());
    }

    #[Test]
    #[DataProvider('unusableValueDataProvider')]
    public function getPendingUserIdReturnsNullForMissingOrUnusableValues(mixed $value): void
    {
        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')->willReturn($value);

        $sut = $this->getSut(session: $sessionStub);

        $this->assertNull($sut->getPendingUserId());
    }

    public static function unusableValueDataProvider(): array
    {
        return [
            'missing' => [null],
            'empty string' => [''],
            'not a string' => [[uniqid()]],
        ];
    }

    #[Test]
    public function clearPendingUserId(): void
    {
        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->once())->method('remove')->with(PendingUserSession::SESSION_KEY);

        $sut = $this->getSut(session: $sessionSpy);

        $sut->clearPendingUserId();
    }

    private function getSut(?SessionInterface $session = null): PendingUserSessionInterface
    {
        return new PendingUserSession(
            session: $session ?? $this->createStub(SessionInterface::class),
        );
    }
}
