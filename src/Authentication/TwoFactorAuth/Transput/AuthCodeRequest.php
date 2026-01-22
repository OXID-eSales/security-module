<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;

readonly class AuthCodeRequest implements AuthCodeRequestInterface
{
    public function __construct(
        private RequestInterface $request,
    ) {
    }

    public function getCode(): string
    {
        return $this->request->get('auth_code');
    }
}
