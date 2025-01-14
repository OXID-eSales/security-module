<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Captcha\Service;

interface CaptchaAudioServiceInterface
{
    public function generate(): string;
}
