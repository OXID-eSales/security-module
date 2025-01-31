<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Service;

use OxidEsales\Eshop\Core\Request;

interface CaptchaServiceInterface
{
    public function getCaptcha(): string;
    public function honeyPotValidate(Request $request): void;
    public function getCaptchaExpiration(): int;
    public function validate(string $captcha): void;
    public function generate(): string;
}
