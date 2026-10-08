<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use DateTimeImmutable;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRendererInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\OtpEmailContentRepositoryInterface;
use OxidEsales\SecurityModule\EmailVerification\Exception\VerificationMailNotSentException;
use OxidEsales\SecurityModule\EmailVerification\Infrastructure\Email\EmailVerificationMailContent;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;
use Psr\Log\LoggerInterface;

class VerificationMailSender implements VerificationMailSenderInterface
{
    private const HTML_TEMPLATE = '@oe_security_module/email/html/emailverification.html.twig';
    private const PLAIN_TEMPLATE = '@oe_security_module/email/plain/emailverification.html.twig';

    public function __construct(
        private UserModelFactoryInterface $userModelFactory,
        private VerificationUrlBuilderInterface $urlBuilder,
        private EmailFactoryInterface $emailFactory,
        private ShopAdapterInterface $shopAdapter,
        private OtpEmailContentRepositoryInterface $contentRepository,
        private OtpMailRendererInterface $renderer,
        private LoggerInterface $logger,
    ) {
    }

    public function sendEmailVerificationMail(
        string $userId,
        #[\SensitiveParameter] string $token,
        DateTimeImmutable $expiresAt
    ): void {
        $user = $this->userModelFactory->create();
        if (!$user->load($userId)) {
            throw new VerificationMailNotSentException();
        }

        $email = (string) $user->getFieldData('oxusername');
        $data = [
            'userName' => $this->getUserName($user, $email),
            'verificationLink' => $this->urlBuilder->buildVerificationLink($token),
            'expiresAt' => $expiresAt->format($this->shopAdapter->translateString('EMAIL_VERIFICATION_DATE_FORMAT')),
        ];

        if ($this->sendFromCmsContent($email, $token, $data) || $this->sendFallback($email, $data)) {
            return;
        }

        $this->logger->error('The email verification mail could not be sent.', ['userId' => $userId]);

        throw new VerificationMailNotSentException();
    }

    /**
     * @param array<string, string> $data
     */
    private function sendFromCmsContent(string $email, #[\SensitiveParameter] string $token, array $data): bool
    {
        $subject = $this->contentRepository->getEmailSubject(EmailVerificationMailContent::IDENT);
        if ($subject === null) {
            return false;
        }

        $mail = $this->emailFactory->create();
        $shop = $mail->getShop();

        try {
            $data += [
                'shopName' => (string) $shop->getFieldData('oxname'),
                'subject' => $subject,
                'contentIdent' => EmailVerificationMailContent::IDENT,
            ];
            $html = $this->renderer->render(self::HTML_TEMPLATE, $data);
            $plain = $this->renderer->render(self::PLAIN_TEMPLATE, $data);
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Rendering the email verification CMS mail template failed; falling back to the built-in mail.',
                [
                    'contentIdent' => EmailVerificationMailContent::IDENT,
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ],
            );

            return false;
        }

        if (!str_contains($html, $token) || !str_contains($plain, $token)) {
            $this->logger->warning(
                'The email verification CMS mail content does not contain the link placeholder;'
                . ' falling back to the built-in mail.',
                ['contentIdent' => EmailVerificationMailContent::IDENT],
            );

            return false;
        }

        $mail->setFrom((string) $shop->getFieldData('oxorderemail'), (string) $shop->getFieldData('oxname'));
        $mail->setSmtp($shop);
        $mail->setSubject($subject);
        $mail->setBody($html);
        $mail->setAltBody($plain);
        $mail->setRecipient($email, $data['userName']);

        if (!$mail->send()) {
            $this->logger->warning(
                'Sending the email verification CMS mail failed; falling back to the built-in mail.',
                ['contentIdent' => EmailVerificationMailContent::IDENT],
            );

            return false;
        }

        return true;
    }

    /**
     * @param array<string, string> $data
     */
    private function sendFallback(string $email, array $data): bool
    {
        $mail = $this->emailFactory->create();
        $shopName = (string) $mail->getShop()->getFieldData('oxname');

        $subject = $this->shopAdapter->translateString('EMAIL_VERIFICATION_EMAIL_SUBJECT');
        $body = sprintf(
            $this->shopAdapter->translateString('EMAIL_VERIFICATION_EMAIL_BODY'),
            $data['userName'],
            $shopName,
            $data['verificationLink'],
            $data['expiresAt'],
        );

        return (bool) $mail->sendEmail($email, $subject, $body);
    }

    private function getUserName(User $user, string $email): string
    {
        $name = trim($user->getFieldData('oxfname') . ' ' . $user->getFieldData('oxlname'));

        return $name !== '' ? $name : $email;
    }
}
