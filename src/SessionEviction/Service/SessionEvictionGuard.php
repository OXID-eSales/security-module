<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;

class SessionEvictionGuard implements SessionEvictionGuardInterface
{
    private const CORE_LOGIN_TOKEN = 'login-token';

    public function __construct(
        private readonly SessionInterface $session
    ) {
    }

    public function hasLoginToken(): bool
    {
        return (string)$this->session->get(self::CORE_LOGIN_TOKEN) !== '';
    }

    public function getUserId(string $userIdParameter): string
    {
        return (string)$this->session->get($userIdParameter);
    }

    public function forgetUserId(string $userIdParameter): void
    {
        $this->session->remove($userIdParameter);
    }
}
