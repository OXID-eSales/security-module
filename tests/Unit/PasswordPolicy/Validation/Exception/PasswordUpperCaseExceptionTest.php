<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Exception;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordUpperCaseException;
use PHPUnit\Framework\TestCase;

class PasswordUpperCaseExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new PasswordUpperCaseException();

        $this->assertInstanceOf(PasswordUpperCaseException::class, $exception);
        $this->assertSame('ERROR_PASSWORD_MISSING_UPPER_CASE', $exception->getMessage());
    }
}
