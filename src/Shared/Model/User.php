<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Model;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionServiceInterface;

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
        if ($sendConfirmEmail || !$verificationFacade->initializeEmailVerification((string) $this->getId())) {
            parent::sendRegistrationEmail($sendConfirmEmail);
        }
    }

    /** @phpstan-ignore missingType.return (inherited from parent without return type) */
    protected function onLogin($userName, #[\SensitiveParameter] $password)
    {
        parent::onLogin($userName, $password);

        $userId = $this->getId();
        if ($userId && !$this->isAdmin()) {
            $this->getService(UserEmailVerificationFacadeInterface::class)->stopUnverifiedLogin($userId);

            $twoFAUserService = $this->getService(TwoFAUserServiceInterface::class);
            if ($twoFAUserService->isTwoFARequired($userId) && !$twoFAUserService->isChallengeVerified($userId)) {
                $twoFAUserService->startChallengeForUser($userId);
            }
        }
    }

    public function loadActiveUser($blForceAdmin = false)
    {
        $sessionEviction = $this->getService(SessionEvictionServiceInterface::class);

        $sessionEviction->evictUnverifiableSession($this, (bool) $blForceAdmin);
        $result = parent::loadActiveUser($blForceAdmin);
        $sessionEviction->bindLoginTokenToSession($this, (bool) $result);

        return $result;
    }

    public function save()
    {
        $sessionEviction = $this->getService(SessionEvictionServiceInterface::class);

        $passwordHashBeforeSave = $sessionEviction->readCurrentPasswordHash($this);
        $result = parent::save();
        $sessionEviction->refreshTokenAfterPasswordChange($this, $passwordHashBeforeSave);

        return $result;
    }
}
