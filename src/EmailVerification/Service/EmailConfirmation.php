<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\UserVerificationRepositoryInterface;

class EmailConfirmation implements EmailConfirmationInterface
{
    public function __construct(
        private PendingVerificationRepositoryInterface $pendingRepository,
        private UserVerificationRepositoryInterface $userRepository,
    ) {
    }

    public function confirmEmail(string $userId): void
    {
        $this->userRepository->markEmailVerified($userId);
        $this->pendingRepository->deletePendingVerification($userId);
    }
}
