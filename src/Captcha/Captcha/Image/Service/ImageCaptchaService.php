<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Service;

class ImageCaptchaService implements ImageCaptchaServiceInterface
{
    public function validate(string $captcha): bool
    {
        //todo: to be implemented
        return true;
    }

    public function generate(): string
    {
        //todo: to be implemented
        return '';
    }
}
