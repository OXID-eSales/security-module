<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\SessionEviction\Service;

use OxidEsales\Eshop\Core\Session;

class SessionEvictionGuard implements SessionEvictionGuardInterface
{
    private const CORE_LOGIN_TOKEN = 'login-token';

    public function __construct(
        private readonly Session $session
    ) {
    }

    public function hasLoginToken(): bool
    {
        return (string)$this->session->getVariable(self::CORE_LOGIN_TOKEN) !== '';
    }

    public function hasUserId(string $userIdParameter): bool
    {
        return (string)$this->session->getVariable($userIdParameter) !== '';
    }

    public function forgetUserId(string $userIdParameter): void
    {
        $this->session->deleteVariable($userIdParameter);
    }

    public function isActingSessionUser(string $userId, string $userIdParameter): bool
    {
        return (string)$this->session->getVariable($userIdParameter) === $userId;
    }
}
