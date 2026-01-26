<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use DateTimeImmutable;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

class ResendOTPService implements ResendOTPServiceInterface
{
    private const RESEND_COOLDOWN = 60;

    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function markAsSent(string $userId): void
    {
        $this->userRepository->markOtpAsSent($userId);
    }

    public function canSend(string $userId): bool
    {
        $otpData = $this->userRepository->getUserOTPData($userId);

        $lastSentAt = $otpData->getLastSentAt();
        if ($lastSentAt === null) {
            return true;
        }

        $now = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $lastSentAt->getTimestamp();

        return $diff >= self::RESEND_COOLDOWN;
    }
}
