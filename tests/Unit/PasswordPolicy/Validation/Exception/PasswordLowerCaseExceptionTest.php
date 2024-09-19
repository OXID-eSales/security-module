<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Exception;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordLowerCaseException;
use PHPUnit\Framework\TestCase;

class PasswordLowerCaseExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new PasswordLowerCaseException();

        $this->assertInstanceOf(PasswordLowerCaseException::class, $exception);
        $this->assertSame('ERROR_PASSWORD_MISSING_LOWER_CASE', $exception->getMessage());
    }
}
