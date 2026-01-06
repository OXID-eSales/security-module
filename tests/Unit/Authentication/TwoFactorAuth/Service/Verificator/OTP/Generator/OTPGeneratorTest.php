<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service\Verificator\OTP\Generator;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Generator\OTPGenerator;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\Verificator\OTP\Generator\OTPGeneratorInterface;
use PHPUnit\Framework\TestCase;
use DateTime;

class OTPGeneratorTest extends TestCase
{
    public function testGenerateCodeCreatesOtpAndPersistsIt(): void
    {
        $userId = uniqid();
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('addOTPtoUser')
            ->with(
                $this->equalTo($userId),
                $this->callback(function (string $otp): bool {
                    return strlen($otp) === 6 && ctype_digit($otp);
                }),
                $this->callback(function (DateTime $expiresAt): bool {
                    return $expiresAt->getTimestamp() > time();
                })
            );

        $generator = $this->getSut($repository);

        $otp = $generator->generateCode($userId);

        $this->assertSame(6, strlen($otp));
        $this->assertTrue(ctype_digit($otp));
    }

    public function getSut(UserRepositoryInterface $repository): OTPGeneratorInterface
    {
        return new OTPGenerator($repository);
    }
}
