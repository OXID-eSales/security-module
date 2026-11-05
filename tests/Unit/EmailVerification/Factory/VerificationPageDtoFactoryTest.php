<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Factory;

use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;
use OxidEsales\SecurityModule\EmailVerification\Factory\VerificationPageDtoFactory;
use OxidEsales\SecurityModule\EmailVerification\Factory\VerificationPageDtoFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VerificationPageDtoFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $sut = $this->getSut();
        $result = $sut->create(
            state: VerificationPageState::EXPIRED,
            token: $token = uniqid(),
            canResend: true,
            errorMessage: $errorMessage = uniqid(),
            errorArgument: $errorArgument = rand(),
        );

        $this->assertSame(VerificationPageState::EXPIRED, $result->getState());
        $this->assertSame($token, $result->getToken());
        $this->assertTrue($result->canResend());
        $this->assertSame($errorMessage, $result->getErrorMessage());
        $this->assertSame($errorArgument, $result->getErrorArgument());
    }

    private function getSut(): VerificationPageDtoFactoryInterface
    {
        return new VerificationPageDtoFactory();
    }
}
