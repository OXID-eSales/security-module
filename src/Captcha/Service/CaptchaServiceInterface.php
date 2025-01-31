<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Service;

use OxidEsales\Eshop\Core\Request;

interface CaptchaServiceInterface
{
    public function validate(Request $request): void;
    public function generate(): array;
}
