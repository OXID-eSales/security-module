<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service\OTPService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service\OTPServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidatorInterface;
use PHPUnit\Framework\TestCase;

class OTPServiceTest extends TestCase
{
    public function testInvalidCode(): void
    {
        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new InvalidCodeException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator
        );

        $this->expectException(InvalidCodeException::class);

        $otpService->validateCode(uniqid());
    }

    public function testExpiredCode(): void
    {
        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new TimeExpiredException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator
        );

        $this->expectException(TimeExpiredException::class);

        $otpService->validateCode(uniqid());
    }

    public function testAttemptsCode(): void
    {
        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new AttemptLimitExceededException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator
        );

        $this->expectException(AttemptLimitExceededException::class);

        $otpService->validateCode(uniqid());
    }

    public function getSut(
        OTPValidatorInterface $otpValidator = null
    ): OTPServiceInterface {
        return new OTPService(
            otpValidator: $otpValidator ?? $this->createMock(OTPValidatorInterface::class)
        );
    }
}
