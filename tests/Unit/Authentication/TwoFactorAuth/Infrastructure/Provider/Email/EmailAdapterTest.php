<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Infrastructure\Provider\Email;

use OxidEsales\Eshop\Core\Email;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\Email\EmailAdapter;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Provider\Factory\EmailFactoryInterface;
use PHPUnit\Framework\TestCase;

class EmailAdapterTest extends TestCase
{
    public function testProviderName(): void
    {
        $sut = $this->getSut();

        $this->assertSame('email', $sut->getName());
    }

    public function testEmailSend(): void
    {
        $recipient = uniqid();
        $code = uniqid();

        $emailModelSpy = $this->createMock(Email::class);
        $emailModelSpy->expects($this->once())
            ->method('sendEmail')
            ->with(
                $this->equalTo($recipient),
                $this->equalTo('Your verification code'),
                $this->equalTo("Your verification code is: {$code}")
            );

        $emailFactoryMock = $this->createMock(EmailFactoryInterface::class);
        $emailFactoryMock->method('create')->willReturn($emailModelSpy);

        $sut = $this->getSut(
            emailFactory: $emailFactoryMock
        );

        $sut->notify($recipient, $code);
    }

    private function getSut(
        EmailFactoryInterface $emailFactory = null
    ): EmailAdapter {
        return new EmailAdapter(
            emailFactory: $emailFactory ?? $this->createStub(EmailFactoryInterface::class)
        );
    }
}
