<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Model;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserServiceInterface;

/**
 * User model extended
 *
 * @mixin \OxidEsales\Eshop\Application\Model\User
 * @eshopExtension
 */
class User extends User_parent
{
    /** @phpstan-ignore missingType.return (inherited from parent without return type) */
    protected function onLogin($userName, #[\SensitiveParameter] $password)
    {
        parent::onLogin($userName, $password);

        $userId = $this->getId();
        if ($userId && !$this->isAdmin()) {
            $twoFAUserService = $this->getService(TwoFAUserServiceInterface::class);
            if ($twoFAUserService->isTwoFARequired($userId) && !$twoFAUserService->isChallengeVerified($userId)) {
                $twoFAUserService->startChallengeForUser($userId);
            }
        }
    }
}
