<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Core;

use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface as PasswordSettingsServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    public function getSecurityModulePasswordSettings(): PasswordSettingsServiceInterface
    {
        return $this->getService(PasswordSettingsServiceInterface::class);
    }

    public function getSecurityModuleCaptchaSettings(): CaptchaSettingsServiceInterface
    {
        return $this->getService(CaptchaSettingsServiceInterface::class);
    }

    public function getPasswordLength(): int
    {
        $passwordLength = $this->getSecurityModulePasswordSettings()->getPasswordMinimumLength();
        $shopPasswordLength = Registry::getInputValidator()->getPasswordLength();

        if ($passwordLength < $shopPasswordLength) {
            return $shopPasswordLength;
        }

        return $passwordLength;
    }

    public function getImage(): string
    {
        $image = $this->getService(CaptchaServiceInterface::class)->generate();

        return 'data:image/jpeg;base64,' . base64_encode($image);
    }
}
