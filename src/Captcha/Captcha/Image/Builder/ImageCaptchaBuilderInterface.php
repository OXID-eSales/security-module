<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder;

interface ImageCaptchaBuilderInterface
{
    public function build(): string;

    public function getContent(): ?string;
}
