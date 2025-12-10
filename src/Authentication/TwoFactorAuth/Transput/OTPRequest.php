<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;

readonly class OTPRequest implements OTPRequestInterface
{
    public function __construct(
        private RequestInterface $request,
    ) {
    }

    public function getOTPCode(): string
    {
        return $this->request->get('auth_code');
    }
}
