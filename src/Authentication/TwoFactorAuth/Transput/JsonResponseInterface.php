<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

interface JsonResponseInterface
{
    public function send(array $data, int $statusCode = 200): void;
}
