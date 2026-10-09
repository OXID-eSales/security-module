<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\EmailVerification\DTO\PendingVerificationDtoInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\EmailAlreadyVerifiedException;
use OxidEsales\SecurityModule\EmailVerification\Exception\InvalidVerificationTokenException;
use OxidEsales\SecurityModule\EmailVerification\Exception\ResendCooldownException;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationTokenExpiredException;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\UserVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;

class EmailVerificationService implements EmailVerificationServiceInterface
{
    public function __construct(
        private EmailVerificationSettingsInterface $settings,
        private PendingVerificationRepositoryInterface $pendingRepository,
        private UserVerificationRepositoryInterface $userRepository,
        private VerificationTokenGeneratorInterface $tokenGenerator,
        private ResendPolicyInterface $resendPolicy,
        private VerificationMailSenderInterface $mailSender,
    ) {
    }

    public function startEmailVerification(string $userId): void
    {
        $token = $this->tokenGenerator->generateToken();
        $expiresAt = $this->getExpiresAt();

        $this->pendingRepository->createPendingVerification($userId, $token, $expiresAt);
        $this->mailSender->sendEmailVerificationMail($userId, $token, $expiresAt);
    }

    public function verifyEmail(#[\SensitiveParameter] string $token): void
    {
        $pendingVerification = $this->findByToken($token);
        if ($pendingVerification->getExpiresAt() < new DateTimeImmutable()) {
            throw new VerificationTokenExpiredException();
        }

        $userId = $pendingVerification->getUserId();
        $this->userRepository->markEmailVerified($userId);
        $this->pendingRepository->deletePendingVerification($userId);
    }

    public function resendVerificationEmailByToken(#[\SensitiveParameter] string $token): void
    {
        $this->resend($this->findByToken($token));
    }

    public function resendVerificationEmailForUser(string $userId): void
    {
        $pendingVerification = $this->pendingRepository->findByUserId($userId);
        if ($pendingVerification === null) {
            throw new EmailAlreadyVerifiedException();
        }

        $this->resend($pendingVerification);
    }

    private function resend(PendingVerificationDtoInterface $pendingVerification): void
    {
        $cooldown = $this->resendPolicy->getCooldownRemaining($pendingVerification);
        if ($cooldown > 0) {
            throw new ResendCooldownException($cooldown);
        }

        $userId = $pendingVerification->getUserId();
        $token = $this->tokenGenerator->generateToken();
        $expiresAt = $this->getExpiresAt();

        $this->mailSender->sendEmailVerificationMail($userId, $token, $expiresAt);
        $this->pendingRepository->refreshPendingVerification($userId, $token, $expiresAt);
    }

    private function findByToken(#[\SensitiveParameter] string $token): PendingVerificationDtoInterface
    {
        $pendingVerification = $this->pendingRepository->findByToken($token);
        if ($pendingVerification === null) {
            throw new InvalidVerificationTokenException();
        }

        return $pendingVerification;
    }

    private function getExpiresAt(): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('+%d hours', $this->settings->getLinkLifetimeHours()));
    }
}
