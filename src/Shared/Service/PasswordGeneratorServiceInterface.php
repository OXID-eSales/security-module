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

interface PasswordGeneratorServiceInterface
{
    public function generatePasswordForOAuthUser(): string;
}
