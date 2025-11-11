<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Exception;

class UserNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('ERROR_USER_NOT_FOUND');
    }
}
