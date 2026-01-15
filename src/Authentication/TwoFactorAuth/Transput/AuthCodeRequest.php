<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\InvalidCodeException;

readonly class AuthCodeRequest implements AuthCodeRequestInterface
{
    public function __construct(
        private RequestInterface $request,
    ) {
    }

    public function getCode(): string
    {
        $code = $this->request->get('auth_code');

        if (!is_string($code) || $code === '') {
            throw new InvalidCodeException();
        }

        return $code;
    }
}
