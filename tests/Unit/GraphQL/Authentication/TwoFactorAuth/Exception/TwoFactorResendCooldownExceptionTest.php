<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\GraphQL\Authentication\TwoFactorAuth\Exception;

use Exception;
use OxidEsales\GraphQL\Base\Exception\ErrorCategories;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Exception\TwoFactorResendCooldownException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TwoFactorResendCooldownExceptionTest extends TestCase
{
    #[Test]
    public function carriesAGenericClientSafeMessage(): void
    {
        $sut = new TwoFactorResendCooldownException();

        $this->assertTrue($sut->isClientSafe(), 'Message must be surfaced to the client');
        $this->assertNotEmpty($sut->getMessage(), 'A generic client-facing message must be present');
        $this->assertSame(ErrorCategories::REQUESTERROR, $sut->getCategory());
    }

    #[Test]
    public function preservesThePreviousDomainException(): void
    {
        $previous = new Exception(uniqid());

        $sut = new TwoFactorResendCooldownException(previous: $previous);

        $this->assertSame($previous, $sut->getPrevious());
    }
}
