<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use DateTimeImmutable;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;

class OtpSendPolicyService implements OtpSendPolicyServiceInterface
{
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        private OtpChallengeStateRepositoryInterface $stateRepository,
    ) {
    }

    public function canSend(string $userId): bool
    {
        return $this->getCooldownRemaining($userId) === 0;
    }

    public function getCooldownRemaining(string $userId): int
    {
        $state = $this->stateRepository->findByUserId($userId);

        if ($state === null) {
            return 0;
        }

        $lastSentAt = $state->getLastSentAt();
        if ($lastSentAt === null) {
            return 0;
        }

        $elapsed = (new DateTimeImmutable())->getTimestamp() - $lastSentAt->getTimestamp();

        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }
}
