<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\MalformedRequestException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TwoFAException;
use PHPUnit\Framework\TestCase;

class MalformedRequestExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new MalformedRequestException();

        $this->assertInstanceOf(TwoFAException::class, $exception);
        $this->assertSame('ERROR_MALFORMED_REQUEST', $exception->getMessage());
    }
}
