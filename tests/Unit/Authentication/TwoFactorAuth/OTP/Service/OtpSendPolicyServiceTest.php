<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpSendPolicyService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpSendPolicyServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpSendPolicyServiceTest extends TestCase
{
    #[Test]
    public function canSendReturnsTrueWhenNoStateExists(): void
    {
        $userId = uniqid();

        $repositoryMock = $this->createMock(OtpChallengeStateRepositoryInterface::class);
        $repositoryMock->method('findByUserId')
            ->with($userId)
            ->willReturn(null);

        $sut = $this->getSut(challengeStateRepository: $repositoryMock);

        $this->assertTrue($sut->canSend($userId));
    }

    #[Test]
    public function canSendReturnsTrueWhenLastSentAtIsNull(): void
    {
        $userId = uniqid();

        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getLastSentAt')
            ->willReturn(null);

        $repositoryMock = $this->createMock(OtpChallengeStateRepositoryInterface::class);
        $repositoryMock->method('findByUserId')
            ->with($userId)
            ->willReturn($stateStub);

        $sut = $this->getSut(challengeStateRepository: $repositoryMock);

        $this->assertTrue($sut->canSend($userId));
    }

    #[Test]
    public function canSendReturnsTrueWhenCooldownHasPassed(): void
    {
        $userId = uniqid();

        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getLastSentAt')
            ->willReturn(new DateTimeImmutable('-61 seconds'));

        $repositoryMock = $this->createMock(OtpChallengeStateRepositoryInterface::class);
        $repositoryMock->method('findByUserId')
            ->with($userId)
            ->willReturn($stateStub);

        $sut = $this->getSut(challengeStateRepository: $repositoryMock);

        $this->assertTrue($sut->canSend($userId));
    }

    #[Test]
    public function canSendReturnsFalseWhenCooldownHasNotPassed(): void
    {
        $userId = uniqid();

        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getLastSentAt')
            ->willReturn(new DateTimeImmutable('-30 seconds'));

        $repositoryMock = $this->createMock(OtpChallengeStateRepositoryInterface::class);
        $repositoryMock->method('findByUserId')
            ->with($userId)
            ->willReturn($stateStub);

        $sut = $this->getSut(challengeStateRepository: $repositoryMock);

        $this->assertFalse($sut->canSend($userId));
    }

    private function getSut(
        OtpChallengeStateRepositoryInterface $challengeStateRepository = null,
    ): OtpSendPolicyServiceInterface {
        return new OtpSendPolicyService(
            challengeStateRepository: $challengeStateRepository ?? $this->createStub(OtpChallengeStateRepositoryInterface::class),
        );
    }
}
