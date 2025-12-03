<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class PasswordGeneratorService implements PasswordGeneratorServiceInterface
{
    public function generatePasswordForOAuthUser(): string
    {
        return bin2hex(random_bytes(20));
    }
}
