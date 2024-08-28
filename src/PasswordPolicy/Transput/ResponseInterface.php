<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordPolicy\Transput;

interface ResponseInterface
{
    public function responseAsJson(array $valueArray): void;
}
