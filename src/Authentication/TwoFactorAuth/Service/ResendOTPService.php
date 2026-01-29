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

    public function markAsSent(string $userName): void
    {
        $otpData = $this->userRepository->getUserOTPData($userName);

        $this->userRepository->markOtpAsSent($otpData->getId());
    }

    public function canSend(string $userName): bool
    {
        $otpData = $this->userRepository->getUserOTPData($userName);

        $lastSentAt = $otpData->getLastSentAt();
        if ($lastSentAt === null) {
            return true;
        }

        $now = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $lastSentAt->getTimestamp();

        return $diff >= self::RESEND_COOLDOWN;
    }
}
