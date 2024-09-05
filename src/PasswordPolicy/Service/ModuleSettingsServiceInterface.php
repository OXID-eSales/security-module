<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Service;

interface ModuleSettingsServiceInterface
{
    public function getPasswordMinimumLength(): int;

    public function getPasswordUppercase(): bool;

    public function getPasswordLowercase(): bool;

    public function getPasswordDigit(): bool;

    public function getPasswordSpecialChar(): bool;
}
