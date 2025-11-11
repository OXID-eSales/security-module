<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\ProviderInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\Provider\ProviderNotFound;

class ProviderCollector implements ProviderCollectorInterface
{
    public function __construct(
        protected iterable $providers,
    ) {
    }

    /**
     * @return array<ProviderInterface>
     */
    public function getProviders(): array
    {
        $result = [];

        foreach ($this->providers as $provider) {
            $result[$provider->getName()] = $provider;
        }

        ksort($result);

        return $result;
    }

    public function getProvider(string $name): ProviderInterface
    {
        $providers = $this->getProviders();

        if (!isset($providers[$name])) {
            throw new ProviderNotFound();
        }

        return $providers[$name];
    }
}
