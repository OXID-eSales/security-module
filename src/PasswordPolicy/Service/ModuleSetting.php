<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\SecurityModule\Core\Module;

class ModuleSetting implements ModuleSettingInterface
{
    public const PASSWORD_MINIMUM_LENGTH = 'oeSecurityPasswordMinimumLength';
    public const PASSWORD_UPPERCASE = 'oeSecurityPasswordContainUppercase';
    public const PASSWORD_LOWERCASE = 'oeSecurityPasswordContainLowercase';
    public const PASSWORD_DIGIT = 'oeSecurityPasswordContainDigit';
    public const PASSWORD_SPECIAL_CHAR = 'oeSecurityPasswordContainSpecialCharacter';

    public function __construct(
        private readonly ModuleSettingServiceInterface $moduleSettingService
    ) {
    }

    public function getPasswordMinimumLength(): int
    {
        return $this->moduleSettingService->getInteger(self::PASSWORD_MINIMUM_LENGTH, Module::MODULE_ID);
    }

    public function getPasswordUppercase(): bool
    {
        return $this->moduleSettingService->getBoolean(self::PASSWORD_UPPERCASE, Module::MODULE_ID);
    }

    public function getPasswordLowercase(): bool
    {
        return $this->moduleSettingService->getBoolean(self::PASSWORD_LOWERCASE, Module::MODULE_ID);
    }

    public function getPasswordDigit(): bool
    {
        return $this->moduleSettingService->getBoolean(self::PASSWORD_DIGIT, Module::MODULE_ID);
    }

    public function getPasswordSpecialChar(): bool
    {
        return $this->moduleSettingService->getBoolean(self::PASSWORD_SPECIAL_CHAR, Module::MODULE_ID);
    }
}
