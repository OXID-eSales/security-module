<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Service;

use OxidEsales\SecurityModule\Captcha\Captcha\CaptchaInterface;

class CaptchaService implements CaptchaServiceInterface
{
    public function __construct(
        private readonly CaptchaInterface $captchaService
    ) {
    }

    public function validate(string $captcha): bool
    {
        return $this->captchaService->validate($captcha);
    }

    public function generate(): string
    {
        return $this->captchaService->generate();
    }
}
