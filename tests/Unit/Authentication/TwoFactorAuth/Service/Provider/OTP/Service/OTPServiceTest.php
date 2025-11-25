<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service\Provider\OTP\Service;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service\OTPService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Service\OTPServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidatorInterface;
use PHPUnit\Framework\TestCase;
use DateTime;

class OTPServiceTest extends TestCase
{
    public function testInvalidCode(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock
            ->method('getFieldData')
            ->willReturnMap([
                ['OTPCODE', uniqid()],
                ['OTPATTEMPTS', 0],
                ['OTPEXPIRETIME', (new DateTime('+1 hour'))->format('Y-m-d H:i:s')],
            ]);

        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new InvalidCodeException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator
        );

        $this->expectException(InvalidCodeException::class);

        $otpService->validateCode($userModelMock, uniqid());
    }

    public function testExpiredCode(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock
            ->method('getFieldData')
            ->willReturnMap([
                ['OTPCODE', uniqid()],
                ['OTPATTEMPTS', 99],
                ['OTPEXPIRETIME', (new DateTime('+1 hour'))->format('Y-m-d H:i:s')],
            ]);

        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new TimeExpiredException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator
        );

        $this->expectException(TimeExpiredException::class);

        $otpService->validateCode(
            $userModelMock,
            uniqid()
        );
    }

    public function testAttemptsCode(): void
    {
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock
            ->method('getFieldData')
            ->willReturnMap([
                ['OTPCODE', uniqid()],
                ['OTPATTEMPTS', 0],
                ['OTPEXPIRETIME', (new DateTime('-1 hour'))->format('Y-m-d H:i:s')],
            ]);

        $otpValidator = $this->createMock(OTPValidatorInterface::class);
        $otpValidator->method('validateCode')->willThrowException(new AttemptLimitExceededException());

        $otpService = $this->getSut(
            otpValidator: $otpValidator
        );

        $this->expectException(AttemptLimitExceededException::class);

        $otpService->validateCode(
            $userModelMock,
            uniqid()
        );
    }

    public function getSut(
        OTPValidatorInterface $otpValidator = null
    ): OTPServiceInterface {
        return new OTPService(
            otpValidator: $otpValidator ?? $this->createMock(OTPValidatorInterface::class)
        );
    }
}
