<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\PasswordPolicy\Validation\Exception;

use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordDigitException;
use PHPUnit\Framework\TestCase;

class PasswordDigitExceptionTest extends TestCase
{
    public function testException()
    {
        $exception = new PasswordDigitException();

        $this->assertInstanceOf(PasswordDigitException::class, $exception);
        $this->assertSame('ERROR_PASSWORD_MISSING_DIGIT', $exception->getMessage());
    }
}
