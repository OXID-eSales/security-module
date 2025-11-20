<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\ProviderInterface;

interface ProviderCollectorInterface
{
    public function getProviders(): array;

    public function getProvider(string $name): ProviderInterface;
}
