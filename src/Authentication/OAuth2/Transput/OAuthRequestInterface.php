<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Transput;

interface OAuthRequestInterface
{
    public function getProvider(): string;

    public function getCode(): string;

    public function hasError(): bool;
}
