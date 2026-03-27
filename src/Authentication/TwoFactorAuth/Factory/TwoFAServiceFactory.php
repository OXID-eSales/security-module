<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Factory;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\AuthenticationTypeNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;

class TwoFAServiceFactory implements TwoFAServiceFactoryInterface
{
    public function __construct(
        private ModuleSettingsServiceInterface $settings,
        private array $implementations,
    ) {
    }

    public function create(): TwoFAServiceInterface
    {
        $type = $this->settings->getTwoFactorAuthType();

        return $this->implementations[$type] ?? throw new AuthenticationTypeNotFoundException();
    }
}
