<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

class TwoFAUserSettingsService implements TwoFAUserSettingsServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function isEnabledForUser(string $userId): bool
    {
        return $this->userRepository->getUserById($userId)->isTwoFAEnabled();
    }

    public function setEnabledForUser(string $userId, bool $enabled): void
    {
        $this->userRepository->setTwoFAEnabled($userId, $enabled);
    }
}
