<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AttemptLimitExceededException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TimeExpiredException;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;

class OtpCodeValidatorService implements OtpCodeValidatorServiceInterface
{
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private OtpChallengeStateRepositoryInterface $repository,
        private OtpCodeHasherServiceInterface $codeHasher,
    ) {
    }

    public function validateCode(
        string $userId,
        #[\SensitiveParameter] string $inputCode
    ): void {
        $state = $this->repository->findByUserId($userId);

        if ($state === null) {
            throw new InvalidCodeException();
        }

        if ($state->getAttempts() >= self::MAX_ATTEMPTS) {
            throw new AttemptLimitExceededException();
        }

        if (new DateTimeImmutable() > $state->getExpiresAt()) {
            throw new TimeExpiredException();
        }

        if (!hash_equals($state->getCodeHash(), $this->codeHasher->hash($inputCode))) {
            $this->repository->incrementAttempts($userId);
            if (($state->getAttempts() + 1) >= self::MAX_ATTEMPTS) {
                throw new AttemptLimitExceededException();
            }
            throw new InvalidCodeException();
        }
    }

    public function getMaxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }
}
