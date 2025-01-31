<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Service;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\SecurityModule\Captcha\Captcha\CaptchaInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Service\HoneyPotCaptchaServiceInterface;

class CaptchaService implements CaptchaServiceInterface
{
    public function __construct(
        private readonly CaptchaInterface $captchaService,
        private readonly HoneyPotCaptchaServiceInterface $honeyPotService,
    ) {
    }

    public function getCaptcha(): string
    {
        return $this->captchaService->getCaptcha();
    }

    public function getCaptchaExpiration(): int
    {
        return $this->captchaService->getCaptchaExpiration();
    }

    public function validate(string $captcha): void
    {
        $this->captchaService->validate($captcha);
    }

    public function honeyPotValidate(Request $request): void
    {
        $this->honeyPotService->validate($request);
    }

    public function generate(): string
    {
        return $this->captchaService->generate();
    }
}
