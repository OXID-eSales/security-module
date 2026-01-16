<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception;

class VerificatorNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('ERROR_VERIFICATOR_NOT_FOUND');
    }
}
