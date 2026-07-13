<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Notifier\Email;

use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailContent;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Email\OtpMailRendererInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\OtpEmailContentRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Email\OtpEmailNotifier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpEmailNotifierTest extends TestCase
{
    #[Test]
    public function notifySendsRenderedHtmlMailWhenCmsContentExists(): void
    {
        $email = uniqid() . '@example.com';
        $code = (string) random_int(100000, 999999);
        $subject = uniqid();
        $html = uniqid() . " $code " . uniqid();
        $plain = uniqid() . " $code";

        $contentRepositoryMock = $this->createMock(OtpEmailContentRepositoryInterface::class);
        $contentRepositoryMock->expects($this->once())
            ->method('getEmailSubject')
            ->with(OtpMailContent::IDENT)
            ->willReturn($subject);

        $rendererMock = $this->createMock(OtpMailRendererInterface::class);
        $rendererMock->expects($this->exactly(2))
            ->method('render')
            ->willReturnCallback(function (string $template, array $data) use ($code, $html, $plain): string {
                $this->assertSame(OtpMailContent::IDENT, $data['contentIdent']);
                $this->assertSame($code, $data['otp']);

                return str_contains($template, '/html/') ? $html : $plain;
            });

        $emailModelMock = $this->createMock(Email::class);
        $emailModelMock->method('getShop')->willReturn($this->createStub(Shop::class));
        $emailModelMock->expects($this->once())->method('setFrom');
        $emailModelMock->expects($this->once())->method('setSubject')->with($subject);
        $emailModelMock->expects($this->once())->method('setBody')->with($html);
        $emailModelMock->expects($this->once())->method('setAltBody')->with($plain);
        $emailModelMock->expects($this->once())->method('setRecipient')->with($email, '');
        $emailModelMock->expects($this->once())->method('send');
        $emailModelMock->expects($this->never())->method('sendEmail');

        $sut = $this->getSut(
            emailFactory: $this->emailFactoryReturning($emailModelMock),
            userRepository: $this->userRepositoryReturning($email),
            contentRepository: $contentRepositoryMock,
            renderer: $rendererMock,
        );

        $sut->notify(userId: uniqid(), code: $code);
    }

    #[Test]
    public function notifyFallsBackToPlainMailWhenNoCmsContent(): void
    {
        $contentRepositoryStub = $this->createStub(OtpEmailContentRepositoryInterface::class);
        $contentRepositoryStub->method('getEmailSubject')->willReturn(null);

        $this->assertPlainFallbackIsSent($contentRepositoryStub, $this->createStub(OtpMailRendererInterface::class));
    }

    #[Test]
    public function notifyFallsBackToPlainMailWhenRendererThrows(): void
    {
        $contentRepositoryStub = $this->createStub(OtpEmailContentRepositoryInterface::class);
        $contentRepositoryStub->method('getEmailSubject')->willReturn(uniqid());

        $rendererStub = $this->createStub(OtpMailRendererInterface::class);
        $rendererStub->method('render')->willThrowException(new \RuntimeException(uniqid()));

        $this->assertPlainFallbackIsSent($contentRepositoryStub, $rendererStub);
    }

    #[Test]
    public function notifyFallsBackToPlainMailWhenRenderedHtmlMissesTheCode(): void
    {
        $contentRepositoryStub = $this->createStub(OtpEmailContentRepositoryInterface::class);
        $contentRepositoryStub->method('getEmailSubject')->willReturn(uniqid());

        $rendererStub = $this->createStub(OtpMailRendererInterface::class);
        $rendererStub->method('render')->willReturn(uniqid());

        $this->assertPlainFallbackIsSent($contentRepositoryStub, $rendererStub);
    }

    private function assertPlainFallbackIsSent(
        OtpEmailContentRepositoryInterface $contentRepository,
        OtpMailRendererInterface $renderer,
    ): void {
        $email = uniqid() . '@example.com';
        $code = (string) random_int(100000, 999999);
        $subject = uniqid();
        $bodyTemplate = uniqid() . ' %s';

        $translations = [
            'OTP_EMAIL_SUBJECT' => $subject,
            'OTP_EMAIL_BODY'    => $bodyTemplate,
        ];
        $shopAdapterStub = $this->createStub(ShopAdapterInterface::class);
        $shopAdapterStub->method('translateString')->willReturnCallback(
            fn(string $key): string => $translations[$key] ?? $key
        );

        $emailModelMock = $this->createMock(Email::class);
        $emailModelMock->expects($this->once())
            ->method('sendEmail')
            ->with($email, $subject, sprintf($bodyTemplate, $code));
        $emailModelMock->expects($this->never())->method('send');

        $sut = $this->getSut(
            emailFactory: $this->emailFactoryReturning($emailModelMock),
            userRepository: $this->userRepositoryReturning($email),
            shopAdapter: $shopAdapterStub,
            contentRepository: $contentRepository,
            renderer: $renderer,
        );

        $sut->notify(userId: uniqid(), code: $code);
    }

    private function emailFactoryReturning(Email $email): EmailFactoryInterface
    {
        $emailFactoryStub = $this->createStub(EmailFactoryInterface::class);
        $emailFactoryStub->method('create')->willReturn($email);

        return $emailFactoryStub;
    }

    private function userRepositoryReturning(string $email): UserRepositoryInterface
    {
        $userStub = $this->createStub(UserInterface::class);
        $userStub->method('getEmail')->willReturn($email);

        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('getUserById')->willReturn($userStub);

        return $userRepositoryStub;
    }

    private function getSut(
        ?EmailFactoryInterface $emailFactory = null,
        ?UserRepositoryInterface $userRepository = null,
        ?ShopAdapterInterface $shopAdapter = null,
        ?OtpEmailContentRepositoryInterface $contentRepository = null,
        ?OtpMailRendererInterface $renderer = null,
    ): OtpEmailNotifier {
        return new OtpEmailNotifier(
            emailFactory: $emailFactory ?? $this->createStub(EmailFactoryInterface::class),
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class),
            shopAdapter: $shopAdapter ?? $this->createStub(ShopAdapterInterface::class),
            contentRepository: $contentRepository ?? $this->createStub(OtpEmailContentRepositoryInterface::class),
            renderer: $renderer ?? $this->createStub(OtpMailRendererInterface::class),
        );
    }
}
