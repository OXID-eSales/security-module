<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\DTO;

use OxidEsales\SecurityModule\EmailVerification\DTO\VerificationPageDto;
use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VerificationPageDtoTest extends TestCase
{
    #[Test]
    public function getState(): void
    {
        $sut = new VerificationPageDto(state: $state = VerificationPageState::VERIFIED);

        $this->assertSame($state, $sut->getState());
    }

    #[Test]
    public function getToken(): void
    {
        $sut = new VerificationPageDto(state: VerificationPageState::EXPIRED, token: $token = uniqid());

        $this->assertSame($token, $sut->getToken());
    }

    #[Test]
    public function canResend(): void
    {
        $sut = new VerificationPageDto(state: VerificationPageState::PENDING, canResend: true);

        $this->assertTrue($sut->canResend());
    }

    #[Test]
    public function getErrorMessage(): void
    {
        $sut = new VerificationPageDto(state: VerificationPageState::PENDING, errorMessage: $message = uniqid());

        $this->assertSame($message, $sut->getErrorMessage());
    }

    #[Test]
    public function getErrorArgument(): void
    {
        $sut = new VerificationPageDto(state: VerificationPageState::PENDING, errorArgument: $argument = rand());

        $this->assertSame($argument, $sut->getErrorArgument());
    }
}
