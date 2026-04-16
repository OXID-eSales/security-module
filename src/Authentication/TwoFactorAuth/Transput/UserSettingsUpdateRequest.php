<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Transput;

use OxidEsales\EshopCommunity\Internal\Framework\Request\RequestInterface;

readonly class UserSettingsUpdateRequest implements UserSettingsUpdateRequestInterface
{
    public function __construct(
        private RequestInterface $request,
    ) {
    }

    public function isTwoFAEnabled(): bool
    {
        return (bool) $this->request->get('twofa_enabled');
    }
}
