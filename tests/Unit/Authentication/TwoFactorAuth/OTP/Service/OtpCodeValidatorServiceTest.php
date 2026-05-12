<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeHasherServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service\OtpCodeValidatorService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpCodeValidatorServiceTest extends TestCase
{
    #[Test]
    public function validateCodeThrowsInvalidCodeExceptionWhenNoChallengeState(): void
    {
        $repositoryStub = $this->createStub(OtpChallengeStateRepositoryInterface::class);
        $repositoryStub->method('findByUserId')->willReturn(null);

        $sut = $this->getSut(repository: $repositoryStub);

        $this->expectException(InvalidCodeException::class);

        $sut->validateCode(uniqid(), uniqid());
    }

    #[Test]
    public function validateCodeThrowsAttemptLimitExceededWhenMaxAttemptsAlreadyReached(): void
    {
        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getAttempts')->willReturn(5);

        $repositoryStub = $this->createStub(OtpChallengeStateRepositoryInterface::class);
        $repositoryStub->method('findByUserId')->willReturn($stateStub);

        $sut = $this->getSut(repository: $repositoryStub);

        $this->expectException(AttemptLimitExceededException::class);

        $sut->validateCode(uniqid(), uniqid());
    }

    #[Test]
    public function validateCodeThrowsTimeExpiredExceptionWhenChallengeExpired(): void
    {
        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getAttempts')->willReturn(0);
        $stateStub->method('getExpiresAt')->willReturn(new DateTimeImmutable('-1 second'));

        $repositoryStub = $this->createStub(OtpChallengeStateRepositoryInterface::class);
        $repositoryStub->method('findByUserId')->willReturn($stateStub);

        $sut = $this->getSut(repository: $repositoryStub);

        $this->expectException(TimeExpiredException::class);

        $sut->validateCode(uniqid(), uniqid());
    }

    #[Test]
    public function validateCodeThrowsInvalidCodeExceptionOnCodeMismatch(): void
    {
        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getAttempts')->willReturn(0);
        $stateStub->method('getExpiresAt')->willReturn(new DateTimeImmutable('+5 minutes'));
        $stateStub->method('getCodeHash')->willReturn('stored-hash');

        $repositoryStub = $this->createStub(OtpChallengeStateRepositoryInterface::class);
        $repositoryStub->method('findByUserId')->willReturn($stateStub);

        $hasherStub = $this->createStub(OtpCodeHasherServiceInterface::class);
        $hasherStub->method('hash')->willReturn('different-hash');

        $sut = $this->getSut(repository: $repositoryStub, codeHasher: $hasherStub);

        $this->expectException(InvalidCodeException::class);

        $sut->validateCode(uniqid(), uniqid());
    }

    #[Test]
    public function validateCodeIncrementsAttemptsOnCodeMismatch(): void
    {
        $userId = uniqid();

        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getAttempts')->willReturn(0);
        $stateStub->method('getExpiresAt')->willReturn(new DateTimeImmutable('+5 minutes'));
        $stateStub->method('getCodeHash')->willReturn('stored-hash');

        $repositorySpy = $this->createMock(OtpChallengeStateRepositoryInterface::class);
        $repositorySpy->method('findByUserId')->willReturn($stateStub);
        $repositorySpy->expects($this->once())
            ->method('incrementAttempts')
            ->with($userId);

        $hasherStub = $this->createStub(OtpCodeHasherServiceInterface::class);
        $hasherStub->method('hash')->willReturn('different-hash');

        $sut = $this->getSut(repository: $repositorySpy, codeHasher: $hasherStub);

        try {
            $sut->validateCode($userId, uniqid());
        } catch (InvalidCodeException) {
        }
    }

    #[Test]
    public function validateCodeThrowsAttemptLimitExceededWhenLastAttemptFails(): void
    {
        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getAttempts')->willReturn(4);
        $stateStub->method('getExpiresAt')->willReturn(new DateTimeImmutable('+5 minutes'));
        $stateStub->method('getCodeHash')->willReturn('stored-hash');

        $repositoryStub = $this->createStub(OtpChallengeStateRepositoryInterface::class);
        $repositoryStub->method('findByUserId')->willReturn($stateStub);

        $hasherStub = $this->createStub(OtpCodeHasherServiceInterface::class);
        $hasherStub->method('hash')->willReturn('different-hash');

        $sut = $this->getSut(repository: $repositoryStub, codeHasher: $hasherStub);

        $this->expectException(AttemptLimitExceededException::class);

        $sut->validateCode(uniqid(), uniqid());
    }

    #[Test]
    public function validateCodeDoesNotThrowOnCorrectCode(): void
    {
        $userId = uniqid();
        $inputCode = uniqid();
        $codeHash = 'abc123';

        $stateStub = $this->createStub(OtpChallengeStateInterface::class);
        $stateStub->method('getAttempts')->willReturn(0);
        $stateStub->method('getExpiresAt')->willReturn(new DateTimeImmutable('+5 minutes'));
        $stateStub->method('getCodeHash')->willReturn($codeHash);

        $repositorySpy = $this->createMock(OtpChallengeStateRepositoryInterface::class);
        $repositorySpy->method('findByUserId')->willReturn($stateStub);
        $repositorySpy->expects($this->never())->method('incrementAttempts');

        $hasherMock = $this->createMock(OtpCodeHasherServiceInterface::class);
        $hasherMock->method('hash')->with($inputCode)->willReturn($codeHash);

        $sut = $this->getSut(repository: $repositorySpy, codeHasher: $hasherMock);

        $sut->validateCode($userId, $inputCode);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function getMaxAttemptsReturnsConfiguredLimit(): void
    {
        $sut = $this->getSut();

        $this->assertSame(5, $sut->getMaxAttempts());
    }

    private function getSut(
        OtpChallengeStateRepositoryInterface $repository = null,
        OtpCodeHasherServiceInterface $codeHasher = null,
    ): OtpCodeValidatorService {
        return new OtpCodeValidatorService(
            repository: $repository ?? $this->createStub(OtpChallengeStateRepositoryInterface::class),
            codeHasher: $codeHasher ?? $this->createStub(OtpCodeHasherServiceInterface::class),
        );
    }
}
