<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\DTO;

use Codeception\PHPUnit\TestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTO;

class UserDTOTest extends TestCase
{
    public function testInitializeAndReadProperties(): void
    {
        $sut = new UserDTO(
            userId: $userId = uniqid(),
            isBlocked: $isBlocked = (bool) rand(0, 1),
        );

        $this->assertSame($userId, $sut->getId());
        $this->assertSame($isBlocked, $sut->isBlocked());
    }
}
