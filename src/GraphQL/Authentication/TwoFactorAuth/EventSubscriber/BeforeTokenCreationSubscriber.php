<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\EventSubscriber;

use DateTimeImmutable;
use OxidEsales\GraphQL\Base\Event\BeforeTokenCreation;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettingsInterface;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\DataType\TwoFAPendingUser;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\TwoFactorClaim;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BeforeTokenCreationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TwoFAShopSettingsInterface $settings,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [BeforeTokenCreation::class => 'onBeforeTokenCreation'];
    }

    public function onBeforeTokenCreation(BeforeTokenCreation $event): void
    {
        if (!$event->getUser() instanceof TwoFAPendingUser) {
            return;
        }

        $challengeExpiresAt = (new DateTimeImmutable())->getTimestamp()
            + $this->settings->getEffectiveChallengeLifetime();

        $event->withClaim(TwoFactorClaim::PENDING, true);
        $event->withClaim(TwoFactorClaim::EXPIRES_AT, $challengeExpiresAt);
    }
}
