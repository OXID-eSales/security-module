<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception;

class CaptchaGenerateException extends \Exception
{
    public function __construct()
    {
        parent::__construct('ERROR_CAPTCHA_GENERATION_FAILED');
    }
}
