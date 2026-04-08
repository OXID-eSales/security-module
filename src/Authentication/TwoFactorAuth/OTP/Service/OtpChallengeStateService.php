<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\DTO\OtpChallengeStateInterface;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;

class OtpChallengeStateService implements OtpChallengeStateServiceInterface
{
    public function __construct(
        private OtpChallengeStateRepositoryInterface $stateRepository,
        private OtpCodeHasherServiceInterface $codeHasher,
    ) {
    }

    public function getChallengeState(string $userId): ?OtpChallengeStateInterface
    {
        return $this->stateRepository->findByUserId($userId);
    }

    public function createChallengeState(string $userId, #[\SensitiveParameter] string $code): void
    {
        // todo-medium: get expiration from settings, test should be improved also to check the value given to repo
        $expiresAt = new DateTimeImmutable('+5 minutes');
        $codeHash = $this->codeHasher->hash($code);

        $this->stateRepository->createChallengeState($userId, $codeHash, $expiresAt);
    }

    // todo-high: challenge if we want this second method to exist.
    public function refreshChallengeState(string $userId, #[\SensitiveParameter] string $code): void
    {
        // todo-medium: get expiration from settings, test should be improved also to check the value given to repo
        $expiresAt = new DateTimeImmutable('+5 minutes');
        $codeHash = $this->codeHasher->hash($code);

        $this->stateRepository->refreshChallengeState($userId, $codeHash, $expiresAt);
    }

    public function markVerified(string $userId): void
    {
        $this->stateRepository->markVerified($userId);
    }

    public function incrementAttempts(string $userId): void
    {
        $this->stateRepository->incrementAttempts($userId);
    }

    public function deleteChallengeState(string $userId): void
    {
        $this->stateRepository->deleteChallengeState($userId);
    }
}
