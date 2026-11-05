<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Exception;

use OxidEsales\SecurityModule\EmailVerification\Exception\ResendCooldownException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResendCooldownExceptionTest extends TestCase
{
    #[Test]
    public function getRemainingSeconds(): void
    {
        $sut = new ResendCooldownException(remainingSeconds: $seconds = rand());

        $this->assertSame($seconds, $sut->getRemainingSeconds());
    }
}
