<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\ProviderNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderAdapterInterface;

interface ProviderCollectorInterface
{
    /**
     * @return array<ProviderAdapterInterface>
     */
    public function getProviders(): array;

    /**
     * @throws ProviderNotFoundException
     */
    public function getProvider(string $name): ProviderAdapterInterface;
}
