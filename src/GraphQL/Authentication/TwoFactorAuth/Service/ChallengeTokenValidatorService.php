<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Service;

use DateTimeImmutable;
use LogicException;
use OxidEsales\GraphQL\Base\Exception\InvalidToken;
use OxidEsales\GraphQL\Base\Service\Token;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\TwoFactorClaim;

final class ChallengeTokenValidatorService implements ChallengeTokenValidatorServiceInterface
{
    private const BASE_REQUIRED = 'graphql-base is required for the oxapi two-factor challenge validation';

    public function __construct(private readonly ?Token $tokenService = null)
    {
    }

    public function validateAndGetUserId(): string
    {
        $tokenService = $this->tokenService();

        if ($tokenService->getTokenClaim(TwoFactorClaim::PENDING, false) !== true) {
            throw new InvalidToken('Not a two-factor challenge token');
        }

        $challengeExpiresAt = (int)$tokenService->getTokenClaim(TwoFactorClaim::EXPIRES_AT, 0);
        if ($challengeExpiresAt < (new DateTimeImmutable())->getTimestamp()) {
            throw new InvalidToken('Two-factor challenge has expired');
        }

        return (string)$tokenService->getTokenClaim(Token::CLAIM_USERID);
    }

    private function tokenService(): Token
    {
        return $this->tokenService ?? throw new LogicException(self::BASE_REQUIRED);
    }
}
