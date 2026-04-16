<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Service;

interface ModuleSettingsServiceInterface
{
    public function isCaptchaEnabled(): bool;

    public function isHoneyPotCaptchaEnabled(): bool;

    public function getCaptchaLifeTime(): string;
}
