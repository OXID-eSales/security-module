<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Settings;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TokenInvalidationServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAUserSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TwoFAUserSettingsTest extends TestCase
{
    #[Test]
    public function enablingFromDisabledInvalidatesTheUsersTokens(): void
    {
        $userId = uniqid();
        $tokenInvalidation = $this->createMock(TokenInvalidationServiceInterface::class);
        $tokenInvalidation->expects($this->once())->method('invalidateForUser')->with($userId);

        $sut = $this->getSut($tokenInvalidation, currentlyEnabled: false);

        $sut->setEnabledForUser($userId, true);
    }

    #[Test]
    public function enablingWhenAlreadyEnabledDoesNotInvalidate(): void
    {
        $tokenInvalidation = $this->createMock(TokenInvalidationServiceInterface::class);
        $tokenInvalidation->expects($this->never())->method('invalidateForUser');

        $sut = $this->getSut($tokenInvalidation, currentlyEnabled: true);

        $sut->setEnabledForUser(uniqid(), true);
    }

    #[Test]
    public function disablingDoesNotInvalidate(): void
    {
        $tokenInvalidation = $this->createMock(TokenInvalidationServiceInterface::class);
        $tokenInvalidation->expects($this->never())->method('invalidateForUser');

        $sut = $this->getSut($tokenInvalidation, currentlyEnabled: true);

        $sut->setEnabledForUser(uniqid(), false);
    }

    private function getSut(
        TokenInvalidationServiceInterface $tokenInvalidation,
        bool $currentlyEnabled,
    ): TwoFAUserSettings {
        $user = $this->createStub(UserInterface::class);
        $user->method('isTwoFAEnabled')->willReturn($currentlyEnabled);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('getUserById')->willReturn($user);
        $userRepository->expects($this->once())->method('setTwoFAEnabled');

        return new TwoFAUserSettings($userRepository, $tokenInvalidation);
    }
}
