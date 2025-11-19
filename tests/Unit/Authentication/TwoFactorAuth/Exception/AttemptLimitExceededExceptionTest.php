<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use PHPUnit\Framework\TestCase;

class AttemptLimitExceededExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new AttemptLimitExceededException();

        $this->assertSame('ERROR_ATTEMPT_LIMIT_EXCEEDED', $exception->getMessage());
    }
}
