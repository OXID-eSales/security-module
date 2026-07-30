<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\GraphQL\Authentication\TwoFactorAuth\EventSubscriber;

use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Base\DataType\UserInterface;
use OxidEsales\GraphQL\Base\Event\BeforeTokenCreation;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettingsInterface;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\DataType\TwoFAPendingUser;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\EventSubscriber\BeforeTokenCreationSubscriber;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\TwoFactorClaim;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BeforeTokenCreationSubscriberTest extends TestCase
{
    #[Test]
    public function stampsPendingAndEffectiveExpiryClaimsWhenUserIsTwoFAPending(): void
    {
        $lifetime = random_int(60, 900);
        $settingsStub = $this->createStub(TwoFAShopSettingsInterface::class);
        $settingsStub->method('getEffectiveChallengeLifetime')->willReturn($lifetime);
        $pendingUser = new TwoFAPendingUser($this->createStub(EshopUserModel::class));

        $stampedClaims = [];
        $eventMock = $this->createMock(BeforeTokenCreation::class);
        $eventMock->method('getUser')->willReturn($pendingUser);
        $eventMock->expects($this->exactly(2))
            ->method('withClaim')
            ->willReturnCallback(
                function (string $name, mixed $value) use (&$stampedClaims, $eventMock): BeforeTokenCreation {
                    $stampedClaims[$name] = $value;
                    return $eventMock;
                }
            );

        $before = time();
        $this->getSut(settings: $settingsStub)->onBeforeTokenCreation($eventMock);
        $after = time();

        $this->assertTrue($stampedClaims[TwoFactorClaim::PENDING]);
        $this->assertGreaterThanOrEqual($before + $lifetime, $stampedClaims[TwoFactorClaim::EXPIRES_AT]);
        $this->assertLessThanOrEqual($after + $lifetime, $stampedClaims[TwoFactorClaim::EXPIRES_AT]);
    }

    #[Test]
    public function doesNotStampClaimWhenUserIsNotTwoFAPending(): void
    {
        $regularUser = $this->createStub(UserInterface::class);

        $eventMock = $this->createMock(BeforeTokenCreation::class);
        $eventMock->method('getUser')->willReturn($regularUser);
        $eventMock->expects($this->never())->method('withClaim');

        $this->getSut()->onBeforeTokenCreation($eventMock);
    }

    #[Test]
    public function subscribesToTheBeforeTokenCreationEvent(): void
    {
        $this->assertArrayHasKey(
            BeforeTokenCreation::class,
            BeforeTokenCreationSubscriber::getSubscribedEvents(),
        );
    }

    private function getSut(?TwoFAShopSettingsInterface $settings = null): BeforeTokenCreationSubscriber
    {
        return new BeforeTokenCreationSubscriber(
            settings: $settings ?? $this->createStub(TwoFAShopSettingsInterface::class),
        );
    }
}
