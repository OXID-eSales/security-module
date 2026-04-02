<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Notifier\Email;

use OxidEsales\Eshop\Core\Email;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\EmailFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\NewUserRepositoryInterface;
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

        $userStub = $this->createStub(NewUserInterface::class);
        $userStub->method('getEmail')->willReturn($email);

        $userRepositoryMock = $this->createMock(NewUserRepositoryInterface::class);
        $userRepositoryMock->expects($this->once())
            ->method('getUserById')
            ->with($userId = uniqid())
            ->willReturn($userStub);

        $emailModelSpy = $this->createMock(Email::class);
        $emailModelSpy->expects($this->once())
            ->method('sendEmail')
            ->with(
                $email,
                'Your verification code',
                "Your verification code is: {$code}"
            );

        $emailFactoryStub = $this->createStub(EmailFactoryInterface::class);
        $emailFactoryStub->method('create')->willReturn($emailModelSpy);

        $sut = $this->getSut(
            emailFactory: $emailFactoryStub,
            userRepository: $userRepositoryMock,
        );

        $sut->notify(userId: $userId, code: $code);
    }

    private function getSut(
        EmailFactoryInterface $emailFactory = null,
        NewUserRepositoryInterface $userRepository = null,
    ): OtpEmailNotifier {
        return new OtpEmailNotifier(
            emailFactory: $emailFactory ?? $this->createStub(EmailFactoryInterface::class),
            userRepository: $userRepository ?? $this->createStub(NewUserRepositoryInterface::class),
        );
    }
}
