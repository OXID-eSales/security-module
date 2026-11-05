<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use OxidEsales\SecurityModule\EmailVerification\DTO\VerificationPageDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Enum\VerificationPageState;
use OxidEsales\SecurityModule\EmailVerification\Exception\EmailAlreadyVerifiedException;
use OxidEsales\SecurityModule\EmailVerification\Factory\VerificationPageDtoFactoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\InvalidVerificationTokenException;
use OxidEsales\SecurityModule\EmailVerification\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationTokenExpiredException;

class VerificationPageService implements VerificationPageServiceInterface
{
    private const CSRF_ERROR = 'ERROR_MESSAGE_NON_MATCHING_CSRF_TOKEN';

    public function __construct(
        private EmailVerificationServiceInterface $verificationService,
        private PendingUserSessionInterface $pendingUserSession,
        private VerificationPageDtoFactoryInterface $pageFactory,
    ) {
    }

    public function showPage(bool $sentRequested): VerificationPageDtoInterface
    {
        if ($this->pendingUserSession->getPendingUserId() === null) {
            return $this->pageFactory->create(state: VerificationPageState::INVALID);
        }

        return $this->pageFactory->create(
            state: $sentRequested ? VerificationPageState::SENT : VerificationPageState::PENDING,
            canResend: true,
        );
    }

    public function verifyEmail(#[\SensitiveParameter] string $token): VerificationPageDtoInterface
    {
        try {
            $this->verificationService->verifyEmail($token);
            $this->pendingUserSession->clearPendingUserId();

            return $this->pageFactory->create(state: VerificationPageState::VERIFIED);
        } catch (VerificationTokenExpiredException) {
            return $this->pageFactory->create(
                state: VerificationPageState::EXPIRED,
                token: $token,
                canResend: true,
            );
        } catch (InvalidVerificationTokenException) {
            return $this->pageFactory->create(state: VerificationPageState::INVALID);
        }
    }

    public function resendEmail(#[\SensitiveParameter] string $token): VerificationPageDtoInterface
    {
        $userId = $this->pendingUserSession->getPendingUserId();

        try {
            match (true) {
                $token !== '' => $this->verificationService->resendVerificationEmailByToken($token),
                $userId !== null => $this->verificationService->resendVerificationEmailForUser($userId),
                default => throw new InvalidVerificationTokenException(),
            };

            return $this->pageFactory->create(state: VerificationPageState::SENT, canResend: $userId !== null);
        } catch (ResendCooldownException $exception) {
            return $this->stayOnPage(
                token: $token,
                errorMessage: $exception->getMessage(),
                errorArgument: (int) ceil($exception->getRemainingSeconds() / 60),
            );
        } catch (VerificationMailNotSentException $exception) {
            return $this->stayOnPage(token: $token, errorMessage: $exception->getMessage());
        } catch (EmailAlreadyVerifiedException) {
            $this->pendingUserSession->clearPendingUserId();

            return $this->pageFactory->create(state: VerificationPageState::ALREADY_VERIFIED);
        } catch (InvalidVerificationTokenException) {
            return $this->pageFactory->create(state: VerificationPageState::INVALID);
        }
    }

    public function rejectResendEmail(#[\SensitiveParameter] string $token): VerificationPageDtoInterface
    {
        return $this->stayOnPage(token: $token, errorMessage: self::CSRF_ERROR);
    }

    private function stayOnPage(
        string $token,
        string $errorMessage,
        int $errorArgument = 0,
    ): VerificationPageDtoInterface {
        return $this->pageFactory->create(
            state: $token !== '' ? VerificationPageState::EXPIRED : VerificationPageState::PENDING,
            token: $token,
            canResend: $token !== '' || $this->pendingUserSession->getPendingUserId() !== null,
            errorMessage: $errorMessage,
            errorArgument: $errorArgument,
        );
    }
}
