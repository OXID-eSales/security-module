<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\MalformedRequestException;

interface AuthCodeRequestInterface
{
    /** @throws MalformedRequestException */
    public function getCode(): string;
}
