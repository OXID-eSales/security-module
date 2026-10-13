<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\EmailVerification\Service;

use Generator;
use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;
use OxidEsales\SecurityModule\EmailVerification\Exception\EmailAlreadyVerifiedException;
use OxidEsales\SecurityModule\EmailVerification\Exception\InvalidVerificationTokenException;
use OxidEsales\SecurityModule\EmailVerification\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationTokenExpiredException;
use OxidEsales\SecurityModule\EmailVerification\Factory\VerificationPageDtoFactory;
use OxidEsales\SecurityModule\EmailVerification\Factory\VerificationPageDtoFactoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\EmailVerificationServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\PendingUserSessionInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationPageService;
use OxidEsales\SecurityModule\EmailVerification\Service\VerificationPageServiceInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;

class VerificationPageServiceTest extends TestCase
{
    #[Test]
    #[DataProvider('requestedPageDataProvider')]
    public function showPage(bool $sentRequested, VerificationPageState $expectedState): void
    {
        $pendingUserSessionStub = $this->createConfiguredStub(PendingUserSessionInterface::class, [
            'getPendingUserId' => uniqid(),
        ]);

        $sut = $this->getSut(
            pendingUserSession: $pendingUserSessionStub,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->showPage(sentRequested: $sentRequested);

        $this->assertSame($expectedState, $result->getState());
        $this->assertTrue($result->canResend());
    }

    public static function requestedPageDataProvider(): Generator
    {
        yield 'sent requested' => [true, VerificationPageState::SENT];
        yield 'nothing requested' => [false, VerificationPageState::PENDING];
    }

    #[Test]
    public function showPageIsInvalidWithoutAPendingUser(): void
    {
        $pendingUserSessionStub = $this->createConfiguredStub(PendingUserSessionInterface::class, [
            'getPendingUserId' => null,
        ]);

        $sut = $this->getSut(
            pendingUserSession: $pendingUserSessionStub,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->showPage(sentRequested: true);

        $this->assertSame(VerificationPageState::INVALID, $result->getState());
        $this->assertFalse($result->canResend());
    }

    #[Test]
    public function verifyEmail(): void
    {
        $verificationServiceSpy = $this->createMock(EmailVerificationServiceInterface::class);
        $verificationServiceSpy->expects($this->once())->method('verifyEmail')->with($token = uniqid());
        $pendingUserSessionSpy = $this->createMock(PendingUserSessionInterface::class);
        $pendingUserSessionSpy->expects($this->once())->method('clearPendingUserId');

        $sut = $this->getSut(
            verificationService: $verificationServiceSpy,
            pendingUserSession: $pendingUserSessionSpy,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->verifyEmail($token);

        $this->assertSame(VerificationPageState::VERIFIED, $result->getState());
        $this->assertFalse($result->canResend());
    }

    #[Test]
    #[DataProvider('verificationFailureDataProvider')]
    public function verifyEmailShowsTheFailure(
        Throwable $exception,
        string $token,
        VerificationPageState $expectedState,
        string $expectedToken,
        bool $expectedCanResend,
    ): void {
        $verificationServiceStub = $this->createStub(EmailVerificationServiceInterface::class);
        $verificationServiceStub->method('verifyEmail')->willThrowException($exception);

        $sut = $this->getSut(
            verificationService: $verificationServiceStub,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->verifyEmail($token);

        $this->assertSame($expectedState, $result->getState());
        $this->assertSame($expectedToken, $result->getToken());
        $this->assertSame($expectedCanResend, $result->canResend());
    }

    public static function verificationFailureDataProvider(): Generator
    {
        yield 'expired token keeps the token for resend' => [
            new VerificationTokenExpiredException(),
            $token = uniqid(),
            VerificationPageState::EXPIRED,
            $token,
            true,
        ];
        yield 'unknown token' => [
            new InvalidVerificationTokenException(),
            uniqid(),
            VerificationPageState::INVALID,
            '',
            false,
        ];
    }

    #[Test]
    public function resendEmail(): void
    {
        $verificationServiceSpy = $this->createMock(EmailVerificationServiceInterface::class);
        $verificationServiceSpy->expects($this->once())
            ->method('resendVerificationEmailByToken')
            ->with($token = uniqid());

        $sut = $this->getSut(
            verificationService: $verificationServiceSpy,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->resendEmail($token);

        $this->assertSame(VerificationPageState::SENT, $result->getState());
        $this->assertSame('', $result->getToken());
    }

    #[Test]
    public function resendEmailForThePendingUserWithoutAToken(): void
    {
        $pendingUserSessionStub = $this->createConfiguredStub(PendingUserSessionInterface::class, [
            'getPendingUserId' => $userId = uniqid(),
        ]);
        $verificationServiceSpy = $this->createMock(EmailVerificationServiceInterface::class);
        $verificationServiceSpy->expects($this->once())->method('resendVerificationEmailForUser')->with($userId);

        $sut = $this->getSut(
            verificationService: $verificationServiceSpy,
            pendingUserSession: $pendingUserSessionStub,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->resendEmail('');

        $this->assertSame(VerificationPageState::SENT, $result->getState());
        $this->assertTrue($result->canResend());
    }

    #[Test]
    public function resendEmailShowsTheWaitingTimeDuringTheCooldown(): void
    {
        $pendingUserSessionStub = $this->createConfiguredStub(PendingUserSessionInterface::class, [
            'getPendingUserId' => uniqid(),
        ]);
        $exception = new ResendCooldownException(remainingSeconds: $seconds = rand(1, 3600));
        $verificationServiceStub = $this->createStub(EmailVerificationServiceInterface::class);
        $verificationServiceStub->method('resendVerificationEmailForUser')->willThrowException($exception);

        $sut = $this->getSut(
            verificationService: $verificationServiceStub,
            pendingUserSession: $pendingUserSessionStub,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->resendEmail('');

        $this->assertSame(VerificationPageState::PENDING, $result->getState());
        $this->assertSame($exception->getMessage(), $result->getErrorMessage());
        $this->assertSame((int) ceil($seconds / 60), $result->getErrorArgument());
        $this->assertTrue($result->canResend());
    }

    #[Test]
    public function resendEmailStaysOnTheExpiredPageWhenTheMailCannotBeSent(): void
    {
        $exception = new VerificationMailNotSentException();
        $verificationServiceStub = $this->createStub(EmailVerificationServiceInterface::class);
        $verificationServiceStub->method('resendVerificationEmailByToken')->willThrowException($exception);

        $sut = $this->getSut(
            verificationService: $verificationServiceStub,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->resendEmail($token = uniqid());

        $this->assertSame(VerificationPageState::EXPIRED, $result->getState());
        $this->assertSame($token, $result->getToken());
        $this->assertSame($exception->getMessage(), $result->getErrorMessage());
        $this->assertTrue($result->canResend());
    }

    #[Test]
    public function resendEmailShowsAlreadyConfirmedAndForgetsThePendingUser(): void
    {
        $pendingUserSessionSpy = $this->createMock(PendingUserSessionInterface::class);
        $pendingUserSessionSpy->method('getPendingUserId')->willReturn(uniqid());
        $pendingUserSessionSpy->expects($this->once())->method('clearPendingUserId');
        $verificationServiceStub = $this->createStub(EmailVerificationServiceInterface::class);
        $verificationServiceStub->method('resendVerificationEmailForUser')
            ->willThrowException(new EmailAlreadyVerifiedException());

        $sut = $this->getSut(
            verificationService: $verificationServiceStub,
            pendingUserSession: $pendingUserSessionSpy,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->resendEmail('');

        $this->assertSame(VerificationPageState::ALREADY_VERIFIED, $result->getState());
        $this->assertFalse($result->canResend());
    }

    #[Test]
    public function resendEmailShowsInvalidWithoutATokenAndWithoutAPendingUser(): void
    {
        $pendingUserSessionStub = $this->createConfiguredStub(PendingUserSessionInterface::class, [
            'getPendingUserId' => null,
        ]);
        $verificationServiceSpy = $this->createMock(EmailVerificationServiceInterface::class);
        $verificationServiceSpy->expects($this->never())->method('resendVerificationEmailForUser');

        $sut = $this->getSut(
            verificationService: $verificationServiceSpy,
            pendingUserSession: $pendingUserSessionStub,
            pageFactory: new VerificationPageDtoFactory(),
        );

        $this->assertSame(VerificationPageState::INVALID, $sut->resendEmail('')->getState());
    }

    #[Test]
    public function rejectResendEmail(): void
    {
        $pendingUserSessionStub = $this->createConfiguredStub(PendingUserSessionInterface::class, [
            'getPendingUserId' => uniqid(),
        ]);

        $sut = $this->getSut(
            pendingUserSession: $pendingUserSessionStub,
            pageFactory: new VerificationPageDtoFactory(),
        );
        $result = $sut->rejectResendEmail('');

        $this->assertSame(VerificationPageState::PENDING, $result->getState());
        $this->assertSame('ERROR_MESSAGE_NON_MATCHING_CSRF_TOKEN', $result->getErrorMessage());
        $this->assertTrue($result->canResend());
    }

    private function getSut(
        ?EmailVerificationServiceInterface $verificationService = null,
        ?PendingUserSessionInterface $pendingUserSession = null,
        ?VerificationPageDtoFactoryInterface $pageFactory = null,
    ): VerificationPageServiceInterface {
        return new VerificationPageService(
            verificationService: $verificationService ?? $this->createStub(EmailVerificationServiceInterface::class),
            pendingUserSession: $pendingUserSession ?? $this->createStub(PendingUserSessionInterface::class),
            pageFactory: $pageFactory ?? $this->createStub(VerificationPageDtoFactoryInterface::class),
        );
    }
}
