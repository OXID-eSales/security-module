<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Infrastructure\Repository\OtpChallengeStateRepositoryInterface;

class OtpSendPolicyService implements OtpSendPolicyServiceInterface
{
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        private OtpChallengeStateRepositoryInterface $challengeStateRepository,
    ) {
    }

    public function canSend(string $userId): bool
    {
        $state = $this->challengeStateRepository->findByUserId($userId);

        if ($state === null) {
            return true;
        }

        $lastSentAt = $state->getLastSentAt();
        if ($lastSentAt === null) {
            return true;
        }

        $elapsed = (new DateTimeImmutable())->getTimestamp() - $lastSentAt->getTimestamp();

        return $elapsed >= self::RESEND_COOLDOWN_SECONDS;
    }
}
