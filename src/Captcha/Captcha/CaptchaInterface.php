<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Captcha;

interface CaptchaInterface
{
    public function getCaptcha(): string;
    public function getCaptchaExpiration(): int;

    public function validate(string $userCaptcha): void;
    public function generate(): string;
}
