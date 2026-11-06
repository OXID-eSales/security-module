<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Controller;

use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsServiceInterface as SessionEvictionSettings;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\RegisterController
 * @eshopExtension
 */
class RegisterController extends RegisterController_parent
{
    public function confirmRegistration()
    {
        $result = parent::confirmRegistration();

        $settingsService = $this->getService(SessionEvictionSettings::class);
        if ($settingsService->isEnabled()) {
            $user = $this->getUser();
            if ($user) {
                $user->setSessionLoginToken();
            }
        }

        return $result;
    }
}
