<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Settings;

interface EmailVerificationSettingsInterface
{
    public function isEmailVerificationEnabled(): bool;

    public function getLinkLifetimeHours(): int;
}
