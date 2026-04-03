<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettingsInterface;

class AuthorizeService implements AuthorizeServiceInterface
{
    public const USER_SESSION_KEY = 'pending_authorized_user';

    public function __construct(
        private TwoFASettingsInterface $moduleSettings,
        private VerificationCollectorServiceInterface $verifyCollector,
        private NotifierCollectorInterface $notifierCollector,
        private ResendOTPServiceInterface $resendOTPService,
        private UserRepositoryInterface $userRepository,
        private SessionInterface $session
    ) {
    }

    public function validate(#[\SensitiveParameter] string $inputCode): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verifyCollector->getVerificator(
            $activeVerificator
        );

        $userId = $this->session->get(self::USER_SESSION_KEY);

        $verificator->validateCode($userId, $inputCode);
    }

    public function generate(): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verifyCollector->getVerificator(
            $activeVerificator
        );

        $userId = $this->session->get(self::USER_SESSION_KEY);

        $OTPCode = $verificator->generate($userId);

        $user = $this->userRepository->getUserOTPData($userId);
        $notifier = $this->notifierCollector->getNotifier('email');
        $notifier->notify($user->getEmail(), $OTPCode);
    }

    public function resend(): bool
    {
        $userId = $this->session->get(self::USER_SESSION_KEY);
        if (!$this->resendOTPService->canSend($userId)) {
            return false;
        }

        $this->generate();
        $this->resendOTPService->markAsSent($userId);

        return true;
    }

    public function getVerificationUrl(): string
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verifyCollector->getVerificator(
            $activeVerificator
        );

        return $verificator->getVerificationUrl();
    }

    public function getRemainingAttempts(): int
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verifyCollector->getVerificator(
            $activeVerificator
        );

        $userId = $this->session->get(self::USER_SESSION_KEY);

        return $verificator->getRemainingAttempts($userId);
    }
}
