<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Notifier\Email;

use OxidEsales\Eshop\Core\Email;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Email\OtpEmailNotifier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpEmailNotifierTest extends TestCase
{
    #[Test]
    public function notifySendsEmailToUserAddress(): void
    {
        $email = uniqid() . '@example.com';
        $code = uniqid();
        $subject = uniqid();
        $bodyTemplate = uniqid() . ' %s';

        $userStub = $this->createStub(UserInterface::class);
        $userStub->method('getEmail')->willReturn($email);

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->expects($this->once())
            ->method('getUserById')
            ->with($userId = uniqid())
            ->willReturn($userStub);

        $translations = [
            'OTP_EMAIL_SUBJECT' => $subject,
            'OTP_EMAIL_BODY'    => $bodyTemplate,
        ];
        $shopAdapterMock = $this->createMock(ShopAdapterInterface::class);
        $shopAdapterMock->method('translateString')->willReturnCallback(
            fn(string $key) => $translations[$key] ?? $key
        );

        $emailModelSpy = $this->createMock(Email::class);
        $emailModelSpy->expects($this->once())
            ->method('sendEmail')
            ->with(
                $email,
                $subject,
                sprintf($bodyTemplate, $code)
            );

        $emailFactoryStub = $this->createStub(EmailFactoryInterface::class);
        $emailFactoryStub->method('create')->willReturn($emailModelSpy);

        $sut = $this->getSut(
            emailFactory: $emailFactoryStub,
            userRepository: $userRepositoryMock,
            shopAdapter: $shopAdapterMock,
        );

        $sut->notify(userId: $userId, code: $code);
    }

    private function getSut(
        EmailFactoryInterface $emailFactory = null,
        UserRepositoryInterface $userRepository = null,
        ShopAdapterInterface $shopAdapter = null,
    ): OtpEmailNotifier {
        return new OtpEmailNotifier(
            emailFactory: $emailFactory ?? $this->createStub(EmailFactoryInterface::class),
            userRepository: $userRepository ?? $this->createStub(UserRepositoryInterface::class),
            shopAdapter: $shopAdapter ?? $this->createStub(ShopAdapterInterface::class),
        );
    }
}
