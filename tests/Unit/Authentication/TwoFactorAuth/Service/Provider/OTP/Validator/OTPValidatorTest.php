<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidator;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Provider\OTP\Validator\OTPValidatorInterface;
use PHPUnit\Framework\TestCase;
use DateTime;

class OTPValidatorTest extends TestCase
{
    public function testValidateCodeThrowNonMatchingCodesException()
    {
        $userCode = uniqid();
        $inputCode = uniqid();

        $OTPValidator = $this->getSut();

        $this->expectException(InvalidCodeException::class);

        $OTPValidator->validateCode($userCode, $inputCode);
    }

    public function testValidateCodeThrowException()
    {
        $userCode = uniqid();

        $OTPValidator = $this->getSut();

        $this->expectException(InvalidCodeException::class);

        $OTPValidator->validateCode($userCode, '');
    }

    public function testCheckLoginAttemptsThrowException()
    {
        $OTPValidator = $this->getSut();

        $this->expectException(AttemptLimitExceededException::class);

        $OTPValidator->checkLoginAttempts(rand(10, 99));
    }

    public function testCheckExpirationTimeThrowException()
    {
        $OTPValidator = $this->getSut();

        $this->expectException(TimeExpiredException::class);

        $OTPValidator->checkExpirationTime(new DateTime('-1 hour'));
    }

    public function getSut(): OTPValidatorInterface
    {
        return new OTPValidator();
    }
}
