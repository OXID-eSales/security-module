<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;

class SessionEvictionService implements SessionEvictionServiceInterface
{
    private const ADMIN_USER_ID_PARAMETER = 'auth';
    private const STOREFRONT_USER_ID_PARAMETER = 'usr';

    public function __construct(
        private ModuleSettingsServiceInterface $settings,
        private SessionEvictionGuardInterface $guard,
        private UserModelFactoryInterface $userModelFactory,
    ) {
    }

    public function evictUnverifiableSession(User $user, bool $forceAdmin): void
    {
        if (!$this->settings->isEnabled() || !$this->settings->isStrict()) {
            return;
        }

        $userIdParameter = $this->userIdParameter($user, $forceAdmin);
        if ($this->guard->getUserId($userIdParameter) !== '' && !$this->guard->hasLoginToken()) {
            $this->guard->forgetUserId($userIdParameter);
        }
    }

    public function bindLoginTokenToSession(User $user, bool $userLoaded): void
    {
        if (!$this->settings->isEnabled() || !$userLoaded) {
            return;
        }

        if (!$this->guard->hasLoginToken()) {
            $user->setSessionLoginToken();
        }
    }

    public function bindLoginTokenToSessionUser(): void
    {
        if (!$this->settings->isEnabled()) {
            return;
        }

        $userId = $this->guard->getUserId(self::STOREFRONT_USER_ID_PARAMETER);
        if ($userId === '') {
            return;
        }

        $user = $this->userModelFactory->create();
        if ($user->load($userId)) {
            $user->setSessionLoginToken();
        }
    }

    private function userIdParameter(User $user, bool $forceAdmin): string
    {
        return ($user->isAdmin() || $forceAdmin)
            ? self::ADMIN_USER_ID_PARAMETER
            : self::STOREFRONT_USER_ID_PARAMETER;
    }
}
