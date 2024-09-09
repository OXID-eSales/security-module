<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Exception;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordMinimumLengthException;
use PHPUnit\Framework\TestCase;

class PasswordMinimumLengthExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new PasswordMinimumLengthException();

        $this->assertInstanceOf(PasswordMinimumLengthException::class, $exception);
        $this->assertSame('ERROR_PASSWORD_MIN_LENGTH', $exception->getMessage());
    }
}
