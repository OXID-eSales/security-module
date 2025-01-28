<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Service;

interface CaptchaServiceInterface
{
    public function getCaptcha(): string;
    public function getCaptchaExpiration(): int;
    public function validate(string $captcha): void;
    public function generate(): string;
}
