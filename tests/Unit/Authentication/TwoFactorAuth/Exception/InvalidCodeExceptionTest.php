<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\CodeValidationException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use PHPUnit\Framework\TestCase;

class InvalidCodeExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new InvalidCodeException();

        $this->assertInstanceOf(CodeValidationException::class, $exception);
        $this->assertSame('ERROR_INVALID_CODE', $exception->getMessage());
    }
}
