<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Service;

use OxidEsales\GraphQL\Base\Infrastructure\RefreshTokenRepositoryInterface;
use OxidEsales\GraphQL\Base\Infrastructure\Token;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TokenInvalidationServiceInterface;

final class TokenInvalidationService implements TokenInvalidationServiceInterface
{
    public function __construct(
        private readonly ?Token $tokenInfrastructure = null,
        private readonly ?RefreshTokenRepositoryInterface $refreshTokenRepository = null,
    ) {
    }

    public function invalidateForUser(string $userId): void
    {
        $this->refreshTokenRepository?->invalidateUserTokens($userId);
        $this->tokenInfrastructure?->invalidateUserTokens($userId);
    }
}
