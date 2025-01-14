<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Captcha;

interface CaptchaInterface
{
    public function validate(string $captcha): void;
    public function generate(): string;
}
