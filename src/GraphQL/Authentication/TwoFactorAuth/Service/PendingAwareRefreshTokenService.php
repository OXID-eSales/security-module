<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Service;

use OxidEsales\GraphQL\Base\DataType\UserInterface;
use OxidEsales\GraphQL\Base\Service\RefreshTokenServiceInterface;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\DataType\TwoFAPendingUser;

final class PendingAwareRefreshTokenService implements RefreshTokenServiceInterface
{
    public function __construct(private readonly RefreshTokenServiceInterface $inner)
    {
    }

    public function createRefreshTokenForUser(UserInterface $user): string
    {
        if ($user instanceof TwoFAPendingUser) {
            return '';
        }

        return $this->inner->createRefreshTokenForUser($user);
    }

    public function refreshToken(string $refreshToken, string $fingerprintHash): string
    {
        return $this->inner->refreshToken($refreshToken, $fingerprintHash);
    }
}
