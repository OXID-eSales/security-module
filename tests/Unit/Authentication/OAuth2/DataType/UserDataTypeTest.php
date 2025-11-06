<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\DataType;

use Codeception\PHPUnit\TestCase;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataType\UserDataType;
use PHPUnit\Framework\Attributes\Test;

class UserDataTypeTest extends TestCase
{
    #[Test]
    public function initializeAndReadProperties(): void
    {
        $sut = new UserDataType(
            firstName: $firstName = uniqid(),
            lastName: $lastName = uniqid(),
            email: $email = uniqid(),
        );

        $this->assertSame($firstName, $sut->getFirstName());
        $this->assertSame($lastName, $sut->getLastName());
        $this->assertSame($email, $sut->getEmail());
    }
}
