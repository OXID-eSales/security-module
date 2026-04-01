<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\OtpFacade;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpChallengeStateServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeValidatorServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OTPServiceTest extends TestCase
{
    #[Test]
    public function isVerifiedReturnsFalseWhenNoChallengeState(): void
    {
        $stateServiceMock = $this->createMock(OtpChallengeStateServiceInterface::class);
        $stateServiceMock->expects($this->once())
            ->method('getChallengeState')
            ->with($userId = uniqid())
            ->willReturn(null);

        $sut = $this->getSut(stateService: $stateServiceMock);

        $this->assertFalse($sut->isVerified(userId: $userId));
    }

    #[Test]
    public function isVerifiedReturnsFalseWhenVerifiedAtIsNull(): void
    {
        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getVerifiedAt')->willReturn(null);

        $stateServiceMock = $this->createMock(OtpChallengeStateServiceInterface::class);
        $stateServiceMock->expects($this->once())
            ->method('getChallengeState')
            ->with($userId = uniqid())
            ->willReturn($stateStub);

        $sut = $this->getSut(stateService: $stateServiceMock);

        $this->assertFalse($sut->isVerified(userId: $userId));
    }

    #[Test]
    public function isVerifiedReturnsFalseWhenExpired(): void
    {
        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getVerifiedAt')->willReturn(new DateTimeImmutable());
        $stateStub->method('getExpiresAt')->willReturn(new DateTimeImmutable('-1 second'));

        $stateServiceMock = $this->createMock(OtpChallengeStateServiceInterface::class);
        $stateServiceMock->expects($this->once())
            ->method('getChallengeState')
            ->with($userId = uniqid())
            ->willReturn($stateStub);

        $sut = $this->getSut(stateService: $stateServiceMock);

        $this->assertFalse($sut->isVerified(userId: $userId));
    }

    #[Test]
    public function isVerifiedReturnsTrueWhenVerifiedAtIsSet(): void
    {
        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getVerifiedAt')->willReturn(new DateTimeImmutable());
        $stateStub->method('getExpiresAt')->willReturn(new DateTimeImmutable('+5 minutes'));

        $stateServiceMock = $this->createMock(OtpChallengeStateServiceInterface::class);
        $stateServiceMock->expects($this->once())
            ->method('getChallengeState')
            ->with($userId = uniqid())
            ->willReturn($stateStub);

        $sut = $this->getSut(stateService: $stateServiceMock);

        $this->assertTrue($sut->isVerified(userId: $userId));
    }

    #[Test]
    public function invalidateChallengeDeletesChallengeState(): void
    {
        $stateServiceSpy = $this->createMock(OtpChallengeStateServiceInterface::class);
        $stateServiceSpy->expects($this->once())
            ->method('deleteChallengeState')
            ->with($userId = uniqid());

        $sut = $this->getSut(stateService: $stateServiceSpy);

        $sut->invalidateChallenge(userId: $userId);
    }

    #[Test]
    public function verifyTriggersCodeValidator(): void
    {
        $codeValidatorSpy = $this->createMock(OtpCodeValidatorServiceInterface::class);
        $codeValidatorSpy->expects($this->once())
            ->method('validateCode')
            ->with($userId = uniqid(), $code = uniqid());

        $sut = $this->getSut(codeValidator: $codeValidatorSpy);

        $sut->verify(userId: $userId, code: $code);
    }

    private function getSut(
        OtpChallengeStateServiceInterface $stateService = null,
        OtpCodeValidatorServiceInterface $codeValidator = null,
    ): OtpFacade {
        return new OtpFacade(
            stateService: $stateService ?? $this->createStub(OtpChallengeStateServiceInterface::class),
            codeValidator: $codeValidator ?? $this->createStub(OtpCodeValidatorServiceInterface::class),
        );
    }
}
