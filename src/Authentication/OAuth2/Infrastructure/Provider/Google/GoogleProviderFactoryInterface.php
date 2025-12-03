<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Google;

use League\OAuth2\Client\Provider\Google as GoogleProvider;

interface GoogleProviderFactoryInterface
{
    public function create(): GoogleProvider;
}
