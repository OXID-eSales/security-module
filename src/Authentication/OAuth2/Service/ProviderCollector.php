<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\ProviderNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderAdapterInterface;
use Symfony\Component\Translation\Provider\ProviderInterface;

class ProviderCollector implements ProviderCollectorInterface
{
    private readonly array $collectedProviders;

    /**
     * @param iterable<ProviderAdapterInterface> $providers
     */
    public function __construct(
        protected iterable $providers,
    ) {
        $this->collectedProviders = iterator_to_array($this->providers, false);
    }

    /**
     * @return array<ProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->collectedProviders;
    }

    public function getProvider(string $name): ProviderAdapterInterface
    {
        $providerFound = array_filter(
            $this->collectedProviders,
            fn ($provider) => $provider->getName() === $name
        );

        if (!$providerFound) {
            throw new ProviderNotFoundException();
        }

        return reset($providerFound);
    }
}
