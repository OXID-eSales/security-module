<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Exception;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordSpecialCharException;
use PHPUnit\Framework\TestCase;

class PasswordSpecialCharExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new PasswordSpecialCharException();

        $this->assertInstanceOf(PasswordSpecialCharException::class, $exception);
        $this->assertSame('ERROR_PASSWORD_MISSING_SPECIAL_CHARACTER', $exception->getMessage());
    }
}
