<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\OTP\Notifier\Factory;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Exception\OtpNotifierNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\Factory\OtpNotifierFactory;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\OTP\Notifier\OtpNotifierInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OtpNotifierFactoryTest extends TestCase
{
    #[Test]
    public function createReturnsNotifierForRegisteredChannel(): void
    {
        $notifier = $this->createStub(OtpNotifierInterface::class);
        $sut = $this->getSut(notifiers: ['email' => $notifier]);

        $result = $sut->create(userId: uniqid());

        $this->assertSame($notifier, $result);
    }

    #[Test]
    public function createThrowsWhenChannelNotRegistered(): void
    {
        $sut = $this->getSut(notifiers: []);

        $this->expectException(OtpNotifierNotFoundException::class);

        $sut->create(userId: uniqid());
    }

    private function getSut(array $notifiers): OtpNotifierFactory
    {
        return new OtpNotifierFactory(
            notifiers: $notifiers,
        );
    }
}
