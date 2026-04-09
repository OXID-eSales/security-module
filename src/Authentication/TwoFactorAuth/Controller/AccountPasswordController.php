<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserSettingsServiceInterface;

/**
 *  todo-critical: remove it if we go with new Security section
 * @mixin \OxidEsales\Eshop\Application\Controller\AccountPasswordController
 * @eshopExtension
 */
class AccountPasswordController extends AccountPasswordController_parent
{
    public function saveTwoFactorAuth(): void
    {
        $user = $this->getUser();
        if (!$user) {
            return;
        }

        $enabled = (bool) Registry::getRequest()->getRequestParameter('twofa_enabled');
        $this->getService(TwoFAUserSettingsServiceInterface::class)->setEnabledForUser($user->getId(), $enabled);
    }

    public function isTwoFAEnabled(): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        return $this->getService(TwoFAUserSettingsServiceInterface::class)->isEnabledForUser($user->getId());
    }
}
