<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\SessionEviction\Service;

interface ModuleSettingsServiceInterface
{
    public function isEnabled(): bool;

    public function isStrict(): bool;
}
