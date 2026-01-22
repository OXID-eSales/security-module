<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\DTO;

use Codeception\PHPUnit\TestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTO;

class OAuth2UserDTOTest extends TestCase
{
    public function testInitializeAndReadProperties(): void
    {
        $sut = new OAuth2UserDTO(
            firstName: $firstName = uniqid(),
            lastName: $lastName = uniqid(),
            email: $email = uniqid(),
        );

        $this->assertSame($firstName, $sut->getFirstName());
        $this->assertSame($lastName, $sut->getLastName());
        $this->assertSame($email, $sut->getEmail());
    }
}
