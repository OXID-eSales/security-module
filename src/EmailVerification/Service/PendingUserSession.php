<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;

class PendingUserSession implements PendingUserSessionInterface
{
    public const SESSION_KEY = 'oesmEmailVerificationPendingUserId';

    public function __construct(
        private SessionInterface $session,
    ) {
    }

    public function setPendingUserId(string $userId): void
    {
        $this->session->set(self::SESSION_KEY, $userId);
    }

    public function getPendingUserId(): ?string
    {
        $userId = $this->session->get(self::SESSION_KEY);

        return is_string($userId) && $userId !== '' ? $userId : null;
    }

    public function clearPendingUserId(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }
}
