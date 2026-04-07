<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Email;

use OxidEsales\Eshop\Core\Language;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\NewUserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\OtpNotifierInterface;

class OtpEmailNotifier implements OtpNotifierInterface
{
    public function __construct(
        private EmailFactoryInterface $emailFactory,
        private NewUserRepositoryInterface $userRepository,
        private Language $language,
    ) {
    }

    public function notify(string $userId, #[\SensitiveParameter] string $code): void
    {
        $email = $this->userRepository->getUserById($userId)->getEmail();

        $this->emailFactory->create()->sendEmail(
            $email,
            $this->language->translateString('OTP_EMAIL_SUBJECT'),
            sprintf($this->language->translateString('OTP_EMAIL_BODY'), $code)
        );
    }
}
