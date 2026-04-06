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
    public function __construct(
        private readonly Utils $utils
    ) {
    }

    public function send(array $data, int $statusCode = 200): void
    {
        $this->utils->setHeader('HTTP/1.1 ' . $statusCode);
        $this->utils->setHeader('Content-Type: application/json');

        $response = (string) json_encode($data);
        $this->utils->showMessageAndExit($response);
    }
}
