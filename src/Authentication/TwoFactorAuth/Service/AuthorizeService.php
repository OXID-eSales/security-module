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

        //todo: use session to get email/username or userId
        $userName = $this->session->get('pending_otp_user');

        $verificator->validateCode($userName, $inputCode);
    }

    public function generate(string $userName): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verificationCollectorService->getVerificator(
            $activeVerificator
        );

        $OTPCode = $verificator->generate($userName);

        //todo: module setting?
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
