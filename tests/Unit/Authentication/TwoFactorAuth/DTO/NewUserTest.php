<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\DTO;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUser;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUserInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NewUserTest extends TestCase
{
    #[Test]
    public function initializeAndReadProperties(): void
    {
        $sut = new NewUser(
            userId: $userId = uniqid(),
            email: $email = uniqid(),
        );

        $this->assertInstanceOf(NewUserInterface::class, $sut);
        $this->assertSame($userId, $sut->getUserId());
        $this->assertSame($email, $sut->getEmail());
    }
}
