<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Facade;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Repository\PendingVerificationRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\EmailVerificationServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Service\PendingUserSessionInterface;
use OxidEsales\SecurityModule\EmailVerification\Settings\EmailVerificationSettingsInterface;

class UserEmailVerificationFacade implements UserEmailVerificationFacadeInterface
{
    public function __construct(
        private EmailVerificationSettingsInterface $settings,
        private PendingVerificationRepositoryInterface $pendingRepository,
        private EmailVerificationServiceInterface $verificationService,
        private PendingUserSessionInterface $pendingUserSession,
    ) {
    }

    public function isEmailVerificationRequired(string $userId): bool
    {
        return $this->settings->isEmailVerificationEnabled()
            && $this->pendingRepository->findByUserId($userId) !== null;
    }

    public function initializeEmailVerification(User $user): bool
    {
        if (!$this->settings->isEmailVerificationEnabled()) {
            return false;
        }

        try {
            $this->verificationService->startEmailVerification((string) $user->getId());
        } catch (VerificationMailNotSentException) {
            return true;
        }

        return true;
    }

    public function rememberPendingUser(string $userId): void
    {
        $this->pendingUserSession->setPendingUserId($userId);
    }
}
