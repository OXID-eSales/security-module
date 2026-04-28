<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Email;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\OtpNotifierInterface;

class OtpEmailNotifier implements OtpNotifierInterface
{
    public function __construct(
        private EmailFactoryInterface $emailFactory,
        private UserRepositoryInterface $userRepository,
        private ShopAdapterInterface $shopAdapter,
    ) {
    }

    public function notify(string $userId, #[\SensitiveParameter] string $code): void
    {
        $email = $this->userRepository->getUserById($userId)->getEmail();

        $subject = $this->shopAdapter->translateString('OTP_EMAIL_SUBJECT');
        $bodyTemplate = $this->shopAdapter->translateString('OTP_EMAIL_BODY');

        $this->emailFactory->create()->sendEmail($email, $subject, sprintf($bodyTemplate, $code));
    }
}
