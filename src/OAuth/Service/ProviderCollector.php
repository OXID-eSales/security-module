<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\OAuth\Service;

class ProviderCollector implements ProviderCollectorInterface
{
    public function __construct(
        protected iterable $providers,
    ) {

    }

    public function getProviders(): iterable
    {
        return $this->providers;
    }
}
