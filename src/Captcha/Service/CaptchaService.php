<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Service;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\SecurityModule\Captcha\Captcha\CaptchaInterface;
use OxidEsales\SecurityModule\Captcha\Service\Exception\InvalidCaptchaTypeException;

class CaptchaService implements CaptchaServiceInterface
{
    public function __construct(
        private iterable $captchas,
    ) {
        foreach ($this->captchas as $captcha) {
            if (!$captcha instanceof CaptchaInterface) {
                throw new InvalidCaptchaTypeException();
            }
        }
    }

    public function validate(Request $request): void
    {
        foreach ($this->captchas as $captcha) {
            if (!$captcha->isEnabled()) {
                continue;
            }

            $captcha->validate($request);
        }
    }

    public function generate(): array
    {
        $captchas = [];
        foreach ($this->captchas as $captcha) {
            if (!$captcha->isEnabled()) {
                continue;
            }

            $captchas[$captcha->getName()] = $captcha->generate();
        }

        return $captchas;
    }
}
