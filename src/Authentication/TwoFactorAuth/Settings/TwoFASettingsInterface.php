<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings;

interface TwoFASettingsInterface
{
    public function isTwoFactorAuthEnabled(): bool;

    public function getTwoFactorAuthType(): string;

    public function getVerificationUrl(): string;
}
