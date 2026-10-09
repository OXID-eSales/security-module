<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\EmailAlreadyVerifiedException;
use OxidEsales\SecurityModule\EmailVerification\Exception\InvalidVerificationTokenException;
use OxidEsales\SecurityModule\EmailVerification\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationTokenExpiredException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\UserVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\EmailVerificationService;
use OxidEsales\SecurityModule\EmailVerification\Service\EmailVerificationServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\ResendPolicyInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationMailSenderInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationTokenGeneratorInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmailVerificationServiceTest extends TestCase
{
    #[Test]
    public function startEmailVerification(): void
    {
        $userId = uniqid();
        $hours = rand(1, 48);
        $expectedExpiry = $this->callback(
            fn(DateTimeImmutable $expiresAt): bool => abs(
                $expiresAt->getTimestamp() - (new DateTimeImmutable("+{$hours} hours"))->getTimestamp()
            ) <= 2
        );

        $settingsStub = $this->createConfiguredStub(EmailVerificationSettingsInterface::class, [
            'getLinkLifetimeHours' => $hours,
        ]);
        $tokenGeneratorStub = $this->createConfiguredStub(VerificationTokenGeneratorInterface::class, [
            'generateToken' => $token = uniqid(),
        ]);

        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->expects($this->once())
            ->method('createPendingVerification')
            ->with($userId, $token, $expectedExpiry);

        $mailSenderSpy = $this->createMock(VerificationMailSenderInterface::class);
        $mailSenderSpy->expects($this->once())
            ->method('sendEmailVerificationMail')
            ->with($userId, $token, $expectedExpiry);

        $sut = $this->getSut(
            settings: $settingsStub,
            pendingRepository: $pendingRepositorySpy,
            tokenGenerator: $tokenGeneratorStub,
            mailSender: $mailSenderSpy,
        );

        $sut->startEmailVerification($userId);
    }

    #[Test]
    public function startEmailVerificationKeepsTheOpenVerificationWhenTheMailFails(): void
    {
        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->expects($this->once())
            ->method('createPendingVerification')
            ->with($userId = uniqid(), $this->isString(), $this->isInstanceOf(DateTimeImmutable::class));
        $pendingRepositorySpy->expects($this->never())->method('deletePendingVerification');

        $mailSenderStub = $this->createStub(VerificationMailSenderInterface::class);
        $mailSenderStub->method('sendEmailVerificationMail')
            ->willThrowException(new VerificationMailNotSentException());

        $sut = $this->getSut(pendingRepository: $pendingRepositorySpy, mailSender: $mailSenderStub);

        $this->expectException(VerificationMailNotSentException::class);

        $sut->startEmailVerification($userId);
    }

    #[Test]
    public function verifyEmail(): void
    {
        $pendingVerificationStub = $this->createConfiguredStub(PendingVerificationDtoInterface::class, [
            'getUserId' => $userId = uniqid(),
            'getExpiresAt' => new DateTimeImmutable('+' . rand(1, 48) . ' hours'),
        ]);

        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->method('findByToken')->with($token = uniqid())->willReturn($pendingVerificationStub);
        $pendingRepositorySpy->expects($this->once())->method('deletePendingVerification')->with($userId);

        $userRepositorySpy = $this->createMock(UserVerificationRepositoryInterface::class);
        $userRepositorySpy->expects($this->once())->method('markEmailVerified')->with($userId);

        $sut = $this->getSut(pendingRepository: $pendingRepositorySpy, userRepository: $userRepositorySpy);

        $sut->verifyEmail($token);
    }

    #[Test]
    public function verifyEmailThrowsForAnUnknownLink(): void
    {
        $pendingRepositoryMock = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositoryMock->method('findByToken')->with($token = uniqid())->willReturn(null);

        $sut = $this->getSut(pendingRepository: $pendingRepositoryMock);

        $this->expectException(InvalidVerificationTokenException::class);

        $sut->verifyEmail($token);
    }

    #[Test]
    public function verifyEmailThrowsForAnExpiredLinkAndKeepsTheVerificationOpen(): void
    {
        $pendingVerificationStub = $this->createConfiguredStub(PendingVerificationDtoInterface::class, [
            'getUserId' => uniqid(),
            'getExpiresAt' => new DateTimeImmutable('-' . rand(1, 48) . ' hours'),
        ]);

        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->method('findByToken')->willReturn($pendingVerificationStub);
        $pendingRepositorySpy->expects($this->never())->method('deletePendingVerification');

        $userRepositorySpy = $this->createMock(UserVerificationRepositoryInterface::class);
        $userRepositorySpy->expects($this->never())->method('markEmailVerified');

        $sut = $this->getSut(pendingRepository: $pendingRepositorySpy, userRepository: $userRepositorySpy);

        $this->expectException(VerificationTokenExpiredException::class);

        $sut->verifyEmail(uniqid());
    }

    #[Test]
    public function resendVerificationEmailByToken(): void
    {
        $pendingVerificationStub = $this->createConfiguredStub(PendingVerificationDtoInterface::class, [
            'getUserId' => $userId = uniqid(),
        ]);

        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->method('findByToken')->with($oldToken = uniqid())->willReturn($pendingVerificationStub);
        $pendingRepositorySpy->expects($this->once())
            ->method('refreshPendingVerification')
            ->with($userId, $newToken = uniqid(), $this->isInstanceOf(DateTimeImmutable::class));

        $tokenGeneratorStub = $this->createConfiguredStub(VerificationTokenGeneratorInterface::class, [
            'generateToken' => $newToken,
        ]);

        $mailSenderSpy = $this->createMock(VerificationMailSenderInterface::class);
        $mailSenderSpy->expects($this->once())
            ->method('sendEmailVerificationMail')
            ->with($userId, $newToken, $this->isInstanceOf(DateTimeImmutable::class));

        $sut = $this->getSut(
            pendingRepository: $pendingRepositorySpy,
            tokenGenerator: $tokenGeneratorStub,
            mailSender: $mailSenderSpy,
        );

        $sut->resendVerificationEmailByToken($oldToken);
    }

    #[Test]
    public function resendVerificationEmailByTokenThrowsForAnUnknownLink(): void
    {
        $pendingRepositoryStub = $this->createConfiguredStub(PendingVerificationRepositoryInterface::class, [
            'findByToken' => null,
        ]);

        $mailSenderSpy = $this->createMock(VerificationMailSenderInterface::class);
        $mailSenderSpy->expects($this->never())->method('sendEmailVerificationMail');

        $sut = $this->getSut(pendingRepository: $pendingRepositoryStub, mailSender: $mailSenderSpy);

        $this->expectException(InvalidVerificationTokenException::class);

        $sut->resendVerificationEmailByToken(uniqid());
    }

    #[Test]
    public function resendVerificationEmailByTokenThrowsDuringTheCooldown(): void
    {
        $pendingVerificationStub = $this->createStub(PendingVerificationDtoInterface::class);
        $pendingRepositoryStub = $this->createConfiguredStub(PendingVerificationRepositoryInterface::class, [
            'findByToken' => $pendingVerificationStub,
        ]);

        $resendPolicyMock = $this->createMock(ResendPolicyInterface::class);
        $resendPolicyMock->method('getCooldownRemaining')
            ->with($pendingVerificationStub)
            ->willReturn($cooldown = rand(1, 3600));

        $mailSenderSpy = $this->createMock(VerificationMailSenderInterface::class);
        $mailSenderSpy->expects($this->never())->method('sendEmailVerificationMail');

        $sut = $this->getSut(
            pendingRepository: $pendingRepositoryStub,
            resendPolicy: $resendPolicyMock,
            mailSender: $mailSenderSpy,
        );

        $this->expectException(ResendCooldownException::class);
        $this->expectExceptionMessage((new ResendCooldownException($cooldown))->getMessage());

        $sut->resendVerificationEmailByToken(uniqid());
    }

    #[Test]
    public function resendVerificationEmailForUser(): void
    {
        $pendingVerificationStub = $this->createConfiguredStub(PendingVerificationDtoInterface::class, [
            'getUserId' => $userId = uniqid(),
        ]);

        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->method('findByUserId')->with($userId)->willReturn($pendingVerificationStub);
        $pendingRepositorySpy->expects($this->once())
            ->method('refreshPendingVerification')
            ->with($userId, $token = uniqid(), $this->isInstanceOf(DateTimeImmutable::class));

        $tokenGeneratorStub = $this->createConfiguredStub(VerificationTokenGeneratorInterface::class, [
            'generateToken' => $token,
        ]);

        $mailSenderSpy = $this->createMock(VerificationMailSenderInterface::class);
        $mailSenderSpy->expects($this->once())
            ->method('sendEmailVerificationMail')
            ->with($userId, $token, $this->isInstanceOf(DateTimeImmutable::class));

        $sut = $this->getSut(
            pendingRepository: $pendingRepositorySpy,
            tokenGenerator: $tokenGeneratorStub,
            mailSender: $mailSenderSpy,
        );

        $sut->resendVerificationEmailForUser($userId);
    }

    #[Test]
    public function resendVerificationEmailForUserThrowsWhenTheAddressIsAlreadyConfirmed(): void
    {
        $pendingRepositoryMock = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositoryMock->method('findByUserId')->with($userId = uniqid())->willReturn(null);

        $mailSenderSpy = $this->createMock(VerificationMailSenderInterface::class);
        $mailSenderSpy->expects($this->never())->method('sendEmailVerificationMail');

        $sut = $this->getSut(pendingRepository: $pendingRepositoryMock, mailSender: $mailSenderSpy);

        $this->expectException(EmailAlreadyVerifiedException::class);

        $sut->resendVerificationEmailForUser($userId);
    }

    #[Test]
    public function resendVerificationEmailForUserKeepsTheOldLinkWhenTheMailFails(): void
    {
        $pendingVerificationStub = $this->createStub(PendingVerificationDtoInterface::class);

        $pendingRepositorySpy = $this->createMock(PendingVerificationRepositoryInterface::class);
        $pendingRepositorySpy->method('findByUserId')->willReturn($pendingVerificationStub);
        $pendingRepositorySpy->expects($this->never())->method('refreshPendingVerification');

        $mailSenderStub = $this->createStub(VerificationMailSenderInterface::class);
        $mailSenderStub->method('sendEmailVerificationMail')
            ->willThrowException(new VerificationMailNotSentException());

        $sut = $this->getSut(pendingRepository: $pendingRepositorySpy, mailSender: $mailSenderStub);

        $this->expectException(VerificationMailNotSentException::class);

        $sut->resendVerificationEmailForUser(uniqid());
    }

    private function getSut(
        ?EmailVerificationSettingsInterface $settings = null,
        ?PendingVerificationRepositoryInterface $pendingRepository = null,
        ?UserVerificationRepositoryInterface $userRepository = null,
        ?VerificationTokenGeneratorInterface $tokenGenerator = null,
        ?ResendPolicyInterface $resendPolicy = null,
        ?VerificationMailSenderInterface $mailSender = null,
    ): EmailVerificationServiceInterface {
        return new EmailVerificationService(
            settings: $settings ?? $this->createStub(EmailVerificationSettingsInterface::class),
            pendingRepository: $pendingRepository ?? $this->createStub(PendingVerificationRepositoryInterface::class),
            userRepository: $userRepository ?? $this->createStub(UserVerificationRepositoryInterface::class),
            tokenGenerator: $tokenGenerator ?? $this->createStub(VerificationTokenGeneratorInterface::class),
            resendPolicy: $resendPolicy ?? $this->createStub(ResendPolicyInterface::class),
            mailSender: $mailSender ?? $this->createStub(VerificationMailSenderInterface::class),
        );
    }
}
