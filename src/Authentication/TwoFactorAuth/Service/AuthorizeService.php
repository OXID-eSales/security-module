<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

class AuthorizeService implements AuthorizeServiceInterface
{
    public function __construct(
        private ModuleSettingsServiceInterface $moduleSettings,
        private VerificationCollectorServiceInterface $verificationCollectorService,
        private NotifierCollectorInterface $notifierCollectorService,
    ) {
    }

    public function validate(string $inputCode): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verificationCollectorService->getVerificator(
            $activeVerificator
        );

        //todo: use session to get user id
        $verificator->validateCode('7b4dfcca4669a8bbfcbd29c77cbc82f3', $inputCode);
    }

    public function generate(): void
    {
        $activeVerificator = $this->moduleSettings->getTwoFactorAuthType();

        $verificator = $this->verificationCollectorService->getVerificator(
            $activeVerificator
        );
        //todo: use session to get user id
        $OTPCode = $verificator->generate(uniqid());

        //todo: module setting?
        $notifier = $this->notifierCollectorService->getNotifier('email');
        $notifier->notify('localhost@localhost.local', $OTPCode);
    }
}
