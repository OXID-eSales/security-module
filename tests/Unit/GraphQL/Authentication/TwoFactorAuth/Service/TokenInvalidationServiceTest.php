<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\GraphQL\Authentication\TwoFactorAuth\Service;

use OxidEsales\GraphQL\Base\Infrastructure\RefreshTokenRepositoryInterface;
use OxidEsales\GraphQL\Base\Infrastructure\Token;
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Service\TokenInvalidationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TokenInvalidationServiceTest extends TestCase
{
    #[Test]
    public function invalidateForUserInvalidatesBothRefreshAndAccessTokens(): void
    {
        $userId = uniqid();

        $tokenInfrastructure = $this->createMock(Token::class);
        $tokenInfrastructure->expects($this->once())
            ->method('invalidateUserTokens')
            ->with($userId);

        $refreshTokenRepository = $this->createMock(RefreshTokenRepositoryInterface::class);
        $refreshTokenRepository->expects($this->once())
            ->method('invalidateUserTokens')
            ->with($userId);

        $sut = new TokenInvalidationService($tokenInfrastructure, $refreshTokenRepository);
        $sut->invalidateForUser($userId);
    }

    #[Test]
    public function invalidateForUserIsNoOpWhenGraphqlBaseIsNotActive(): void
    {
        $this->expectNotToPerformAssertions();

        $sut = new TokenInvalidationService(tokenInfrastructure: null, refreshTokenRepository: null);
        $sut->invalidateForUser(uniqid());
    }
}
