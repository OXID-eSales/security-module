<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook;

use League\OAuth2\Client\Provider\Facebook as FacebookProvider;

interface FacebookProviderFactoryInterface
{
    public function create(): FacebookProvider;
}
