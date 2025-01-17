<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Transput;

interface ResponseInterface
{
    public function responseAsImage(string $value): void;
    public function responseAsAudio(string $audio): void;
}
