<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Shop\Core;

use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface;

/**
 * Class ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    public function getSecurityModuleSettings(): ModuleSettingsServiceInterface
    {
        return $this->getService(ModuleSettingsServiceInterface::class);
    }
}
