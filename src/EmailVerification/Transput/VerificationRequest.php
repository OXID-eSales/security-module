<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;

readonly class VerificationRequest implements VerificationRequestInterface
{
    public function __construct(
        private RequestInterface $request,
    ) {
    }

    public function getToken(): string
    {
        $token = $this->request->get('token');

        return is_string($token) && ctype_xdigit($token) ? $token : '';
    }

    public function isSentStateRequested(): bool
    {
        return $this->request->get('state') === 'sent';
    }
}
