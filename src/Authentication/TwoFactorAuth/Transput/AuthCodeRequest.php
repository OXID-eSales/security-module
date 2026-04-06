<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\MalformedRequestException;

readonly class AuthCodeRequest implements AuthCodeRequestInterface
{
    public function __construct(
        private RequestInterface $request,
    ) {
    }

    public function getCode(): string
    {
        $raw = $this->request->get('auth_code');
        if (!is_string($raw) && !is_int($raw)) {
            throw new MalformedRequestException();
        }

        return (string) $raw;
    }
}
