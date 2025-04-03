<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\OAuth\Service;

interface ProviderCollectorInterface
{
    public function getProviders(): iterable;
}
