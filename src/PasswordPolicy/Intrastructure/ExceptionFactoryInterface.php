<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\PasswordPolicy\Intrastructure;

use OxidEsales\EshopCommunity\Core\Exception\InputException;
use OxidEsales\SecurityModule\PasswordPolicy\Validation\Exception\PasswordValidateException;

interface ExceptionFactoryInterface
{
    public function create(PasswordValidateException $exception): InputException;
}
