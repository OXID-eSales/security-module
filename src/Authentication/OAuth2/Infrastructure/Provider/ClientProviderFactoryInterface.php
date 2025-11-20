<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;

interface ClientProviderFactoryInterface
{
    public function create(): AbstractProvider;
}
