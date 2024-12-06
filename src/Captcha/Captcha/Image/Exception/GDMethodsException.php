<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception;

class GDMethodsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('ERROR_GD_METHODS_MISSING');
    }
}
