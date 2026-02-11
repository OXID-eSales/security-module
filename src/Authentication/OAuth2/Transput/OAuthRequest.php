<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Transput;

use OxidEsales\Eshop\Core\Request;

readonly class OAuthRequest implements OAuthRequestInterface
{
    public function __construct(
        private Request $request,
    ) {
    }

    public function getProvider(): string
    {
        return (string) $this->request->getRequestParameter('provider');
    }

    public function getCode(): string
    {
        return (string) $this->request->getRequestParameter('code');
    }

    public function hasError(): bool
    {
        return (bool) $this->request->getRequestParameter('error');
    }
}
