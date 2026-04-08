<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\DTO;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    #[Test]
    public function initializeAndReadProperties(): void
    {
        $sut = new User(
            userId: $userId = uniqid(),
            email: $email = uniqid(),
        );

        $this->assertInstanceOf(UserInterface::class, $sut);
        $this->assertSame($userId, $sut->getUserId());
        $this->assertSame($email, $sut->getEmail());
    }
}
