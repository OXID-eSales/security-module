<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

interface ModuleSettingsServiceInterface
{
    public function isTwoFactorAuthEnabled(): bool;

    public function getTwoFactorAuthType(): string;
}
