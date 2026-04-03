<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettingsInterface;

class TwoFAUserService implements TwoFAUserServiceInterface
{
    public const USER_SESSION_KEY = 'pending_authorized_user';

    public function __construct(
        private TwoFAServiceInterface $twoFAService,
        private TwoFASettingsInterface $settings,
        private Utils $utils,
        private SessionInterface $session,
        private UserModelFactoryInterface $userFactory,
        private InternalRedirectServiceInterface $redirectService,
    ) {
    }

    public function startChallengeForUser(string $userId): void
    {
        $this->session->set(self::USER_SESSION_KEY, $userId);
        $this->twoFAService->triggerChallenge($userId);
        $this->utils->redirect($this->settings->getVerificationUrl());
    }

    public function getPendingUserId(): string
    {
        return $this->session->get(self::USER_SESSION_KEY);
    }

    public function loginUser(string $userId): void
    {
        $this->session->remove(self::USER_SESSION_KEY);

        $user = $this->userFactory->create();
        $user->load($userId);

        /** @phpstan-ignore argument.type (password is null because user already authenticated to come here) */
        $user->login($user->getFieldData('oxusername'), null, false);

        $this->utils->redirect($this->redirectService->getRedirectUrl(), false);
    }

    public function isChallengeVerified(string $userId): bool
    {
        return $this->twoFAService->isVerified($userId);
    }
}
