<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use Symfony\Component\String\UnicodeString;

interface ModuleSettingsServiceInterface
{
    public function isTwoFactorAuthEnabled(): bool;

    public function getTwoFactorAuthType(): string;
}
