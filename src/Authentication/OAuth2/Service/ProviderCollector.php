<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

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
