<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Captcha;

use OxidEsales\Eshop\Core\Request;

interface CaptchaInterface
{
    public function getName(): string;
    public function isEnabled(): bool;

    public function validate(Request $request): void;
    public function generate(): string;
}
