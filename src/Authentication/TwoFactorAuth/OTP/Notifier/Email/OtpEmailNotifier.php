<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Email;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailContent;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRendererInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\OtpEmailContentRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\OtpNotifierInterface;

class OtpEmailNotifier implements OtpNotifierInterface
{
    private const HTML_TEMPLATE = '@oe_security_module/email/html/twofactorotp.html.twig';
    private const PLAIN_TEMPLATE = '@oe_security_module/email/plain/twofactorotp.html.twig';

    public function __construct(
        private EmailFactoryInterface $emailFactory,
        private UserRepositoryInterface $userRepository,
        private ShopAdapterInterface $shopAdapter,
        private OtpEmailContentRepositoryInterface $contentRepository,
        private OtpMailRendererInterface $renderer,
    ) {
    }

    public function notify(string $userId, #[\SensitiveParameter] string $code): void
    {
        $email = $this->userRepository->getUserById($userId)->getEmail();

        if ($this->sendFromCmsContent($email, $code)) {
            return;
        }

        $this->sendFallback($email, $code);
    }

    private function sendFromCmsContent(string $email, #[\SensitiveParameter] string $code): bool
    {
        $subject = $this->contentRepository->getEmailSubject(OtpMailContent::IDENT);
        if ($subject === null) {
            return false;
        }

        try {
            $data = ['otp' => $code, 'contentIdent' => OtpMailContent::IDENT];
            $html = $this->renderer->render(self::HTML_TEMPLATE, $data);
            $plain = $this->renderer->render(self::PLAIN_TEMPLATE, $data);
        } catch (\Throwable) {
            return false;
        }

        if (!str_contains($html, $code) || !str_contains($plain, $code)) {
            return false;
        }

        $mail = $this->emailFactory->create();
        $shop = $mail->getShop();
        // Mirror Core\Email::setMailParams() (protected): a manual multipart send must set
        // the sender and SMTP transport from the shop, just like sendEmail() does internally.
        $mail->setFrom((string) $shop->getFieldData('oxorderemail'), (string) $shop->getFieldData('oxname'));
        $mail->setSmtp($shop);
        $mail->setSubject($subject);
        $mail->setBody($html);
        $mail->setAltBody($plain);
        $mail->setRecipient($email, '');
        $mail->send();

        return true;
    }

    private function sendFallback(string $email, #[\SensitiveParameter] string $code): void
    {
        $subject = $this->shopAdapter->translateString('OTP_EMAIL_SUBJECT');
        $bodyTemplate = $this->shopAdapter->translateString('OTP_EMAIL_BODY');

        $this->emailFactory->create()->sendEmail($email, $subject, sprintf($bodyTemplate, $code));
    }
}
