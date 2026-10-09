<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Model;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;

/**
 * User model extended
 *
 * @mixin \OxidEsales\Eshop\Application\Model\User
 * @eshopExtension
 */
class User extends User_parent
{
    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function sendRegistrationEmail(bool $sendConfirmEmail = false): void
    {
        $verificationFacade = $this->getService(UserEmailVerificationFacadeInterface::class);
        if ($sendConfirmEmail || !$verificationFacade->initializeEmailVerification($this)) {
            parent::sendRegistrationEmail($sendConfirmEmail);
        }
    }

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
