<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\ProviderNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderInterface;

class ProviderCollector implements ProviderCollectorInterface
{
    /**
     * @param iterable<ProviderInterface> $providers
     */
    public function __construct(
        protected iterable $providers,
    ) {
    }

    public function getProviders(): array
    {
        return iterator_to_array($this->providers, false);
    }

    public function getProvider(string $name): ProviderInterface
    {
        $providerFound = array_filter(
            iterator_to_array($this->providers, false),
            fn ($provider) => $provider->getName() === $name
        );

        if (!$providerFound) {
            throw new ProviderNotFoundException();
        }

        return reset($providerFound);
    }
}
