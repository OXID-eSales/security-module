<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\{
    DTO\OtpChallengeStateInterface,
    Infrastructure\Repository\OtpChallengeStateRepositoryInterface,
};

class OtpChallengeStateService implements OtpChallengeStateServiceInterface
{
    public function __construct(
        private OtpChallengeStateRepositoryInterface $challengeStateRepository,
        private OtpCodeHasherServiceInterface $codeHasher,
    ) {
    }

    public function getChallengeState(string $userId): ?OtpChallengeStateInterface
    {
        return $this->challengeStateRepository->findByUserId($userId);
    }

    public function createChallengeState(string $userId, #[\SensitiveParameter] string $code): void
    {
        // todo-medium: get expiration from settings, test should be improved also to check the value given to repo
        $expiresAt = new DateTimeImmutable('+5 minutes');
        $codeHash = $this->codeHasher->hash($code);

        $this->challengeStateRepository->createChallengeState($userId, $codeHash, $expiresAt);
    }

    // todo-high: challenge if we want this second method to exist.
    public function refreshChallengeState(string $userId, #[\SensitiveParameter] string $code): void
    {
        // todo-medium: get expiration from settings, test should be improved also to check the value given to repo
        $expiresAt = new DateTimeImmutable('+5 minutes');
        $codeHash = $this->codeHasher->hash($code);

        $this->challengeStateRepository->refreshChallengeState($userId, $codeHash, $expiresAt);
    }

    public function markVerified(string $userId): void
    {
        $this->challengeStateRepository->markVerified($userId);
    }

    public function incrementAttempts(string $userId): void
    {
        $this->challengeStateRepository->incrementAttempts($userId);
    }

    public function deleteChallengeState(string $userId): void
    {
        $this->challengeStateRepository->deleteChallengeState($userId);
    }
}
