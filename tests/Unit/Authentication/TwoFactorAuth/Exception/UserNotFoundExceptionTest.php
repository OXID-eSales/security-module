<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TwoFAException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;
use PHPUnit\Framework\TestCase;

class UserNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(TwoFAException::class, new UserNotFoundException());
    }
}
