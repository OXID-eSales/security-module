<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service\Verificator\OTP;

use DateTime;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Generator\OTPGeneratorInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\OTPVerificator;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Validator\OTPValidatorInterface;
use PHPUnit\Framework\TestCase;

class OTPVerificatorTest extends TestCase
{
    public function testVerificatorName()
    {
        $otpService = $this->getSut();

        $this->assertEquals('otp', $otpService->getName());
    }

    public function testInvalidCode(): void
    {
        $userId = uniqid();
        $attempts = rand();
        $code = uniqid();

        $userDTOMock = $this->createMock(User::class);
        $userDTOMock->method('getCode')->willReturn($code);
        $userDTOMock->method('getAttempts')->willReturn($attempts);
        $userDTOMock->method('getExpiresAt')->willReturn(new DateTime());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock
            ->method('getUserOTPData')
            ->willReturn($userDTOMock);

        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new InvalidCodeException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator,
            userRepository: $userRepositoryMock,
        );

        $this->expectException(InvalidCodeException::class);

        $otpService->validateCode($userId, $code);
    }

    public function testExpiredCode(): void
    {
        $userId = uniqid();
        $attempts = rand();
        $code = uniqid();

        $userDTOMock = $this->createMock(User::class);
        $userDTOMock->method('getCode')->willReturn($code);
        $userDTOMock->method('getAttempts')->willReturn($attempts);
        $userDTOMock->method('getExpiresAt')->willReturn(new DateTime());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock
            ->method('getUserOTPData')
            ->willReturn($userDTOMock);

        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new TimeExpiredException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator,
            userRepository: $userRepositoryMock,
        );

        $this->expectException(TimeExpiredException::class);

        $otpService->validateCode($userId, $code);
    }

    public function testAttemptsCode(): void
    {
        $userId = uniqid();
        $attempts = rand();
        $code = uniqid();

        $userDTOMock = $this->createMock(User::class);
        $userDTOMock->method('getCode')->willReturn($code);
        $userDTOMock->method('getAttempts')->willReturn($attempts);
        $userDTOMock->method('getExpiresAt')->willReturn(new DateTime());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock
            ->method('getUserOTPData')
            ->willReturn($userDTOMock);

        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new AttemptLimitExceededException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator,
            userRepository: $userRepositoryMock,
        );

        $this->expectException(AttemptLimitExceededException::class);

        $otpService->validateCode($userId, $code);
    }

    public function getSut(
        OTPGeneratorInterface $otpGenerator = null,
        OTPValidatorInterface $otpValidator = null,
        UserRepositoryInterface $userRepository = null
    ): OTPVerificator {
        return new OTPVerificator(
            otpGenerator: $otpGenerator ?? $this->createStub(OTPGeneratorInterface::class),
            otpValidator: $otpValidator ?? $this->createStub(OTPValidatorInterface::class),
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class)
        );
    }
}
