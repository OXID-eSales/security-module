<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;

interface TwoFAUserSettingsInterface
{
    /** @throws UserNotFoundException */
    public function isEnabledForUser(string $userId): bool;

    /** @throws UserNotFoundException */
    public function setEnabledForUser(string $userId, bool $enabled): void;
}
