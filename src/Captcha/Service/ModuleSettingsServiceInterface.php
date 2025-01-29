<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Service;

interface ModuleSettingsServiceInterface
{
    public function isCaptchaEnabled(): bool;

    public function saveIsCaptchaEnabled(bool $value): void;

    public function getCaptchaLifeTime(): string;
}
