<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\GraphQL\Authentication\TwoFactorAuth\Controller;

use LogicException;
use OxidEsales\GraphQL\Base\Service\Token;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAUserSettingsInterface;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\GraphQL\Authentication\TwoFactorAuth\Controller\TwoFactorAuthSettingsController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TwoFactorAuthSettingsControllerTest extends TestCase
{
    #[Test]
    public function setTwoFactorAuthEnablesPreferenceForAuthenticatedUser(): void
    {
        $userId = uniqid();

        $userSettingsSpy = $this->createMock(TwoFAUserSettingsInterface::class);
        $userSettingsSpy->expects($this->once())->method('setEnabledForUser')->with($userId, true);
        $userSettingsSpy->method('isEnabledForUser')->with($userId)->willReturn(true);

        $sut = $this->getSut(
            userSettings: $userSettingsSpy,
            tokenService: $this->tokenStubFor($userId),
        );

        $this->assertTrue($sut->setTwoFactorAuth(true));
    }

    #[Test]
    public function setTwoFactorAuthDisablesPreferenceForAuthenticatedUser(): void
    {
        $userId = uniqid();

        $userSettingsSpy = $this->createMock(TwoFAUserSettingsInterface::class);
        $userSettingsSpy->expects($this->once())->method('setEnabledForUser')->with($userId, false);
        $userSettingsSpy->method('isEnabledForUser')->with($userId)->willReturn(false);

        $sut = $this->getSut(
            userSettings: $userSettingsSpy,
            tokenService: $this->tokenStubFor($userId),
        );

        $this->assertFalse($sut->setTwoFactorAuth(false));
    }

    #[Test]
    public function setTwoFactorAuthThrowsWhenGraphqlBaseUnavailable(): void
    {
        $sut = $this->getSut(tokenService: null);

        $this->expectException(LogicException::class);

        $sut->setTwoFactorAuth(true);
    }

    private function tokenStubFor(string $userId): Token
    {
        $stub = $this->createStub(Token::class);
        $stub->method('getTokenClaim')->willReturn($userId);

        return $stub;
    }

    private function getSut(
        ?TwoFAUserSettingsInterface $userSettings = null,
        ?Token $tokenService = null,
    ): TwoFactorAuthSettingsController {
        return new TwoFactorAuthSettingsController(
            userSettings: $userSettings ?? $this->createStub(TwoFAUserSettingsInterface::class),
            tokenService: $tokenService,
        );
    }
}
