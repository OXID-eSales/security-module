<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Service;

use OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder\ImageCaptchaBuilderInterface;

class ImageCaptchaService implements ImageCaptchaServiceInterface
{
    public function __construct(
        private readonly ImageCaptchaBuilderInterface $captchaBuilder
    ) {
    }

    public function validate(string $captcha): bool
    {
        if ($captcha !== $this->captchaBuilder->getContent()) {
            return false;
        }

        return true;
    }

    public function generate(): string
    {
        $_SESSION['captcha'] = $this->captchaBuilder->getContent();

        return $this->captchaBuilder->build();
    }
}
