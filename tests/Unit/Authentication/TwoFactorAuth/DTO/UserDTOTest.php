<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\DTO;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User;
use PHPUnit\Framework\TestCase;
use DateTime;

class UserDTOTest extends TestCase
{
    public function testInitializeAndReadProperties(): void
    {
        $sut = new User(
            userId: $userId = uniqid(),
            attempts: $attempts = rand(),
            code: $code = uniqid(),
            expiresAt: $expiresAt = new DateTime(),
            lastSentAt: $lastSentAt = new DateTime(),
        );

        $this->assertSame($userId, $sut->getId());
        $this->assertSame($code, $sut->getCode());
        $this->assertSame($attempts, $sut->getAttempts());
        $this->assertSame($expiresAt, $sut->getExpiresAt());
        $this->assertSame($lastSentAt, $sut->getLastSentAt());
    }
}
