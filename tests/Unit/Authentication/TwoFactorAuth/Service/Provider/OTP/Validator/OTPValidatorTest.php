<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidator;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidatorInterface;
use PHPUnit\Framework\TestCase;

class OTPValidatorTest extends TestCase
{
    public function testValidateCode()
    {
        $userCode = uniqid();

        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock
            ->method('getFieldData')
            ->with('OTPCODE')
            ->willReturn(1);

        $OTPValidator = $this->getSut(
            userModel: $userModelMock
        );

        $this->expectException(InvalidCodeException::class);

        $OTPValidator->validateCode($userCode);
    }

    public function testValidateCodeThrowException()
    {
        $OTPValidator = $this->getSut();

        $this->expectException(InvalidCodeException::class);

        $OTPValidator->validateCode('');
    }

    public function testCheckLoginAttemptsThrowException()
    {
        //todo: use setting for limit
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock
            ->method('getFieldData')
            ->with('OTPATTEMPTS')
            ->willReturn(6);

        $OTPValidator = $this->getSut(
            userModel: $userModelMock
        );

        $this->expectException(AttemptLimitExceededException::class);

        $OTPValidator->checkLoginAttempts();
    }

    public function testCheckExpirationTimeThrowException()
    {
        //todo: use setting for limit
        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock
            ->method('getFieldData')
            ->with('OTPEXPIRETIME')
            ->willReturn(99);

        $OTPValidator = $this->getSut(
            userModel: $userModelMock
        );

        $this->expectException(TimeExpiredException::class);

        $OTPValidator->checkExpirationTime();
    }

    public function getSut(
        UserModel $userModel = null
    ): OTPValidatorInterface {
        return new OTPValidator(
            $userModel ?? $this->createMock(UserModel::class)
        );
    }
}
