<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Exception;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\NotifierNotFoundException;
use PHPUnit\Framework\TestCase;

class NotifierNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new NotifierNotFoundException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
