<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AuthenticationTypeNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TwoFAException;
use PHPUnit\Framework\TestCase;

class AuthenticationTypeNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(TwoFAException::class, new AuthenticationTypeNotFoundException());
    }
}
