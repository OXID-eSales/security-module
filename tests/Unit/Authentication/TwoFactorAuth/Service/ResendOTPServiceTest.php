<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ResendOTPService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ResendOTPServiceInterface;
use PHPUnit\Framework\TestCase;

class ResendOTPServiceTest extends TestCase
{
    protected function getSut(
        UserRepositoryInterface $userRepository = null,
    ): ResendOTPServiceInterface {
        return new ResendOTPService(
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class),
        );
    }

    public function testMarkAsSentDelegatesToRepository(): void
    {
        $userId = uniqid();

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->expects($this->once())
            ->method('markOtpAsSent')
            ->with($userId);

        $sut = $this->getSut(
            userRepository: $userRepositoryMock,
        );

        $sut->markAsSent($userId);
    }

    public function testCanSendReturnsTrueWhenNeverSent(): void
    {
        $otpDataStub = $this->createStub(UserInterface::class);
        $otpDataStub->method('getLastSentAt')->willReturn(null);

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($otpDataStub);

        $sut = $this->getSut(
            userRepository: $userRepositoryStub,
        );

        $this->assertTrue($sut->canSend(uniqid()));
    }

    public function testCanSendReturnsFalseWhenCooldownActive(): void
    {
        $lastSent = (new DateTimeImmutable())->modify('-30 seconds');

        $otpDataStub = $this->createStub(UserInterface::class);
        $otpDataStub->method('getLastSentAt')->willReturn($lastSent);

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($otpDataStub);

        $sut = $this->getSut(
            userRepository: $userRepositoryStub,
        );

        $this->assertFalse($sut->canSend(uniqid()));
    }

    public function testCanSendReturnsTrueWhenCooldownPassed(): void
    {
        $lastSent = (new DateTimeImmutable())->modify('-120 seconds');

        $otpDataStub = $this->createStub(UserInterface::class);
        $otpDataStub->method('getLastSentAt')->willReturn($lastSent);

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserOTPData')->willReturn($otpDataStub);

        $sut = $this->getSut(
            userRepository: $userRepositoryStub,
        );

        $this->assertTrue($sut->canSend(uniqid()));
    }
}
