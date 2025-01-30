<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Service;

interface ModuleSettingsServiceInterface
{
    public function isPasswordPolicyEnabled(): bool;

    public function saveIsPasswordPolicyEnabled(bool $value): void;

    public function getPasswordMinimumLength(): int;

    public function getPasswordUppercase(): bool;

    public function getPasswordLowercase(): bool;

    public function getPasswordDigit(): bool;

    public function getPasswordSpecialChar(): bool;
}
