<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;

class AuthorizeService implements AuthorizeServiceInterface
{
    public const USER_SESSION_KEY = 'pending_authorized_user';

    public const OTP_TARGET_URL = 'otp_target_url';

    public function __construct(
        private ModuleSettingsServiceInterface $moduleSettings,
        private VerificationCollectorServiceInterface $verifyCollector,
        private NotifierCollectorInterface $notifierCollector,
        private ResendOTPServiceInterface $resendOTPService,
        private SessionInterface $session
    ) {
    }

    public function validate(string $inputCode): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verifyCollector->getVerificator(
            $activeVerificator
        );

        $userName = $this->session->get(self::USER_SESSION_KEY);

        $verificator->validateCode($userName, $inputCode);
    }

    public function generate(): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verifyCollector->getVerificator(
            $activeVerificator
        );

        $userName = $this->session->get(self::USER_SESSION_KEY);
        if (!$this->resendOTPService->canSend($userName)) {
            return;
        }

        $OTPCode = $verificator->generate($userName);

        $notifier = $this->notifierCollector->getNotifier('email');
        $notifier->notify($userName, $OTPCode);
        $this->resendOTPService->markAsSent($userName);
    }

    public function getVerificationUrl(): string
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verifyCollector->getVerificator(
            $activeVerificator
        );

        return $verificator->getVerificationUrl();
    }
}
