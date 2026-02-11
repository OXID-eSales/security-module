<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

interface JsonResponseInterface
{
    public function setStatusCode(int $code): void;

    public function send(array $data): void;
}
