<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\VerificatorNotFoundException;
use PHPUnit\Framework\TestCase;

class VerificatorNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new VerificatorNotFoundException();

        $this->assertSame('ERROR_VERIFICATOR_NOT_FOUND', $exception->getMessage());
    }
}
