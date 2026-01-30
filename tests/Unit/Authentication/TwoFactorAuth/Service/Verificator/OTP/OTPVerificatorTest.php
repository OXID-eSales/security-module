<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service\Verificator\OTP;

use DateTime;
use OxidEsales\Eshop\Core\Config;
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
    public function testVerificatorName(): void
    {
        $sut = $this->getSut();

        $this->assertEquals('otp', $sut->getName());
    }

    public function testInvalidCode(): void
    {
        $userId = uniqid();
        $code = uniqid();

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn($code);
        $userDTOStub->method('getAttempts')->willReturn(1);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime('+5 minutes'));

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($userDTOStub);

        $otpValidatorStub = $this->createStub(OTPValidatorInterface::class);
        $otpValidatorStub->method('validateCode')->willThrowException(new InvalidCodeException());

        $sut = $this->getSut(
            otpValidator: $otpValidatorStub,
            userRepository: $userRepositoryStub,
        );

        $this->expectException(InvalidCodeException::class);

        $sut->validateCode($userId, $code);
    }

    public function testExpiredCode(): void
    {
        $userId = uniqid();
        $code = uniqid();

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn($code);
        $userDTOStub->method('getAttempts')->willReturn(1);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime());

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($userDTOStub);

        $otpValidatorStub = $this->createStub(OTPValidatorInterface::class);
        $otpValidatorStub->method('checkExpirationTime')->willThrowException(new TimeExpiredException());

        $sut = $this->getSut(
            otpValidator: $otpValidatorStub,
            userRepository: $userRepositoryStub,
        );

        $this->expectException(TimeExpiredException::class);

        $sut->validateCode($userId, $code);
    }

    public function testAttemptsExceeded(): void
    {
        $userId = uniqid();
        $code = uniqid();

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn($code);
        $userDTOStub->method('getAttempts')->willReturn(5);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime('+5 minutes'));

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($userDTOStub);

        $otpValidatorStub = $this->createStub(OTPValidatorInterface::class);
        $otpValidatorStub->method('checkLoginAttempts')->willThrowException(new AttemptLimitExceededException());

        $sut = $this->getSut(
            otpValidator: $otpValidatorStub,
            userRepository: $userRepositoryStub,
        );

        $this->expectException(AttemptLimitExceededException::class);

        $sut->validateCode($userId, $code);
    }

    public function testValidateCodeSuccessResetsOtpData(): void
    {
        $userId = uniqid();
        $code = uniqid();

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn($code);
        $userDTOStub->method('getAttempts')->willReturn(1);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime('+5 minutes'));

        $userRepositorySpy = $this->createMock(UserRepositoryInterface::class);
        $userRepositorySpy->method('getUserOTPData')->willReturn($userDTOStub);
        $userRepositorySpy->expects($this->once())
            ->method('resetCodeFields')
            ->with($userId);
        $userRepositorySpy->expects($this->never())
            ->method('updateAttempts');

        $otpValidatorSpy = $this->createMock(OTPValidatorInterface::class);
        $otpValidatorSpy->expects($this->once())
            ->method('checkLoginAttempts')
            ->with(1);
        $otpValidatorSpy->expects($this->once())
            ->method('checkExpirationTime');
        $otpValidatorSpy->expects($this->once())
            ->method('validateCode')
            ->with($code, $code);

        $sut = $this->getSut(
            otpValidator: $otpValidatorSpy,
            userRepository: $userRepositorySpy,
        );

        $sut->validateCode($userId, $code);
    }

    public function testValidateCodeUpdatesAttemptsOnInvalidCode(): void
    {
        $userId = uniqid();
        $code = uniqid();
        $currentAttempts = 2;

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn('stored-code');
        $userDTOStub->method('getAttempts')->willReturn($currentAttempts);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime('+5 minutes'));

        $userRepositorySpy = $this->createMock(UserRepositoryInterface::class);
        $userRepositorySpy->method('getUserOTPData')->willReturn($userDTOStub);
        $userRepositorySpy->expects($this->once())
            ->method('updateAttempts')
            ->with($userId, $currentAttempts + 1);
        $userRepositorySpy->expects($this->never())
            ->method('resetCodeFields');

        $otpValidatorStub = $this->createStub(OTPValidatorInterface::class);
        $otpValidatorStub->method('validateCode')->willThrowException(new InvalidCodeException());

        $sut = $this->getSut(
            otpValidator: $otpValidatorStub,
            userRepository: $userRepositorySpy,
        );

        $this->expectException(InvalidCodeException::class);

        $sut->validateCode($userId, $code);
    }

    public function testGenerateReturnsGeneratedOtp(): void
    {
        $userId = uniqid();
        $code = uniqid();

        $otpGeneratorSpy = $this->createMock(OTPGeneratorInterface::class);
        $otpGeneratorSpy->expects($this->once())
            ->method('generateCode')
            ->with($userId)
            ->willReturn($code);

        $sut = $this->getSut(
            otpGenerator: $otpGeneratorSpy,
        );

        $this->assertSame($code, $sut->generate($userId));
    }

    public function testGetVerificationUrl(): void
    {
        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn($url = uniqid());

        $sut = new OTPVerificator(
            otpGenerator: $this->createStub(OTPGeneratorInterface::class),
            otpValidator: $this->createStub(OTPValidatorInterface::class),
            userRepository: $this->createStub(UserRepositoryInterface::class),
            config: $configStub,
        );

        $this->assertSame($url . 'cl=twofactorauth', $sut->getVerificationUrl());
    }

    public function getSut(
        OTPGeneratorInterface $otpGenerator = null,
        OTPValidatorInterface $otpValidator = null,
        UserRepositoryInterface $userRepository = null,
    ): OTPVerificator {
        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn(uniqid());

        return new OTPVerificator(
            otpGenerator: $otpGenerator ?? $this->createStub(OTPGeneratorInterface::class),
            otpValidator: $otpValidator ?? $this->createStub(OTPValidatorInterface::class),
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class),
            config: $configStub,
        );
    }
}
