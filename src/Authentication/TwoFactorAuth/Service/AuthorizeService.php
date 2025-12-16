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
        private VerificationCollectorServiceInterface $verificationCollectorService,
        private NotifierCollectorInterface $notifierCollectorService,
        private SessionInterface $session
    ) {
    }

    public function validate(string $inputCode): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verificationCollectorService->getVerificator(
            $activeVerificator
        );

        $userName = $this->session->get(self::USER_SESSION_KEY);

        $verificator->validateCode($userName, $inputCode);
    }

    public function generate(): void
    {
        $userName = $this->session->get(self::USER_SESSION_KEY);

        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verificationCollectorService->getVerificator(
            $activeVerificator
        );

        $OTPCode = $verificator->generate($userName);

        $notifier = $this->notifierCollectorService->getNotifier('email');
        $notifier->notify($userName, $OTPCode);
    }

    public function getVerificationUrl(): string
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verificationCollectorService->getVerificator(
            $activeVerificator
        );

        return $verificator->getVerificationUrl();
    }
}
