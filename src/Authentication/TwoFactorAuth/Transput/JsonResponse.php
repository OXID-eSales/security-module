<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

use OxidEsales\Eshop\Core\Utils;

class JsonResponse implements JsonResponseInterface
{
    private int $statusCode = 200;

    public function __construct(
        private readonly Utils $utils
    ) {
    }

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    public function send(array $data): void
    {
        $this->utils->setHeader('HTTP/1.1 ' . $this->statusCode);
        $this->utils->setHeader('Content-Type: application/json');
        $this->utils->showMessageAndExit(json_encode($data));
    }
}
