<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

class AuthorizeService
{
    public function validate()
    {
        //todo: call correct provider service to validate the code
    }

    public function generate($userName)
    {
        //todo: call correct provider service to generate the code
    }
}
