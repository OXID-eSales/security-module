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
        $otpData = $this->createMock(UserInterface::class);
        $otpData->method('getId')->willReturn($userId = uniqid());

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('getUserOTPData')->willReturn($otpData);
        $userRepository->expects($this->once())
            ->method('markOtpAsSent')
            ->with($userId);

        $service = $this->getSut(
            userRepository: $userRepository,
        );

        $service->markAsSent(uniqid());
    }

    public function testCanSendReturnsTrueWhenNeverSent(): void
    {
        $otpData = $this->createMock(UserInterface::class);
        $otpData->method('getLastSentAt')->willReturn(null);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('getUserOTPData')->willReturn($otpData);

        $service = $this->getSut(
            userRepository: $userRepository,
        );

        $this->assertTrue($service->canSend(uniqid()));
    }

    public function testCanSendReturnsFalseWhenCooldownActive(): void
    {
        $lastSent = (new DateTimeImmutable())->modify('-30 seconds');

        $otpData = $this->createMock(UserInterface::class);
        $otpData->method('getLastSentAt')->willReturn($lastSent);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('getUserOTPData')->willReturn($otpData);

        $service = $this->getSut(
            userRepository: $userRepository,
        );

        $this->assertFalse($service->canSend(uniqid()));
    }

    public function testCanSendReturnsTrueWhenCooldownPassed(): void
    {
        $lastSent = (new DateTimeImmutable())->modify('-120 seconds');

        $otpData = $this->createMock(UserInterface::class);
        $otpData->method('getLastSentAt')->willReturn($lastSent);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('getUserOTPData')->willReturn($otpData);

        $service = $this->getSut(
            userRepository: $userRepository,
        );

        $this->assertTrue($service->canSend(uniqid()));
    }
}
