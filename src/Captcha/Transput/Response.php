<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Transput;

use OxidEsales\Eshop\Core\Utils;

class Response implements ResponseInterface
{
    public function __construct(
        private readonly Utils $utils
    ) {
    }

    public function responseAsImage(string $value): void
    {
        $this->utils->setHeader('Content-Type: text/html; charset=UTF-8');
        $this->utils->showMessageAndExit('data:image/jpeg;base64,' . $value);
    }
}
