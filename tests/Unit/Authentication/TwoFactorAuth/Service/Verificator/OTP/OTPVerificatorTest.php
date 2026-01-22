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
    public function testVerificatorName()
    {
        $otpService = $this->getSut();

        $this->assertEquals('otp', $otpService->getName());
    }

    public function testInvalidCode(): void
    {
        $userName = uniqid();
        $attempts = rand();
        $code = uniqid();

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn($code);
        $userDTOStub->method('getAttempts')->willReturn($attempts);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock
            ->method('getUserOTPData')
            ->willReturn($userDTOStub);

        $otpValidatorMock = $this->createMock(OTPValidatorInterface::class);
        $otpValidatorMock->method('validateCode')->willThrowException(new InvalidCodeException());

        $sut = $this->getSut(
            otpValidator: $otpValidatorMock,
            userRepository: $userRepositoryMock,
        );

        $this->expectException(InvalidCodeException::class);

        $sut->validateCode($userName, $code);
    }

    public function testExpiredCode(): void
    {
        $userName = uniqid();
        $attempts = rand();
        $code = uniqid();

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn($code);
        $userDTOStub->method('getAttempts')->willReturn($attempts);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock
            ->method('getUserOTPData')
            ->willReturn($userDTOStub);

        $otpValidatorMock = $this->createMock(OTPValidatorInterface::class);
        $otpValidatorMock->method('validateCode')->willThrowException(new TimeExpiredException());

        $sut = $this->getSut(
            otpValidator: $otpValidatorMock,
            userRepository: $userRepositoryMock,
        );

        $this->expectException(TimeExpiredException::class);

        $sut->validateCode($userName, $code);
    }

    public function testAttemptsCode(): void
    {
        $userName = uniqid();
        $attempts = rand();
        $code = uniqid();

        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getCode')->willReturn($code);
        $userDTOStub->method('getAttempts')->willReturn($attempts);
        $userDTOStub->method('getExpiresAt')->willReturn(new DateTime());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock
            ->method('getUserOTPData')
            ->willReturn($userDTOStub);

        $otpValidatorMock = $this->createMock(OTPValidatorInterface::class);
        $otpValidatorMock->method('validateCode')->willThrowException(new AttemptLimitExceededException());

        $sut = $this->getSut(
            otpValidator: $otpValidatorMock,
            userRepository: $userRepositoryMock,
        );

        $this->expectException(AttemptLimitExceededException::class);

        $sut->validateCode($userName, $code);
    }

    public function testValidateCodeSuccessResetsOtpData(): void
    {
        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getId')->willReturn($userId = uniqid());
        $userDTOStub->method('getCode')->willReturn($code = uniqid());
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

        $otpService = $this->getSut(
            otpValidator: $otpValidatorSpy,
            userRepository: $userRepositorySpy,
        );

        $otpService->validateCode('user', $code);
    }

    public function testGenerateReturnsGeneratedOtp(): void
    {
        $userDTOStub = $this->createStub(User::class);
        $userDTOStub->method('getId')->willReturn($userId = uniqid());

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($userDTOStub);

        $otpGeneratorSpy = $this->createMock(OTPGeneratorInterface::class);
        $otpGeneratorSpy->expects($this->once())
            ->method('generateCode')
            ->with($userId)
            ->willReturn($code = uniqid());

        $otpService = $this->getSut(
            otpGenerator: $otpGeneratorSpy,
            userRepository: $userRepositoryStub,
        );

        $this->assertSame(
            $code,
            $otpService->generate('user')
        );
    }

    public function testGetVerificationUrl(): void
    {
        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn($url = uniqid());

        $otpService = new OTPVerificator(
            otpGenerator: $this->createStub(OTPGeneratorInterface::class),
            otpValidator: $this->createStub(OTPValidatorInterface::class),
            userRepository: $this->createStub(UserRepositoryInterface::class),
            config: $configStub,
        );

        $this->assertSame(
            $url . 'cl=twofactorauth',
            $otpService->getVerificationUrl()
        );
    }

    public function getSut(
        OTPGeneratorInterface $otpGenerator = null,
        OTPValidatorInterface $otpValidator = null,
        UserRepositoryInterface $userRepository = null
    ): OTPVerificator {
        $configStub = $this->createStub(Config::class);
        $configStub->method('getShopHomeUrl')->willReturn(uniqid());

        return new OTPVerificator(
            otpGenerator: $otpGenerator ?? $this->createStub(OTPGeneratorInterface::class),
            otpValidator: $otpValidator ?? $this->createStub(OTPValidatorInterface::class),
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class),
            config: $configStub
        );
    }
}
