<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFASettingsInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TwoFAUserServiceTest extends TestCase
{
    #[Test]
    public function startChallengeForUserStoresPendingUserTriggersChallengeAndRedirects(): void
    {
        $userId = uniqid();

        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->once())
            ->method('set')
            ->with(TwoFAUserService::USER_SESSION_KEY, $userId);

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->once())
            ->method('triggerChallenge')
            ->with($userId);

        $settingsStub = $this->createStub(TwoFASettingsInterface::class);
        $settingsStub->method('getVerificationUrl')->willReturn($verificationUrl = uniqid());

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect')
            ->with($verificationUrl);

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            settings: $settingsStub,
            utils: $utilsSpy,
            session: $sessionSpy,
        );

        $sut->startChallengeForUser($userId);
    }

    #[Test]
    public function getPendingUserIdReturnsUserIdFromSession(): void
    {
        $sessionStub = $this->createStub(SessionInterface::class);
        $sessionStub->method('get')
            ->with(TwoFAUserService::USER_SESSION_KEY)
            ->willReturn($userId = uniqid());

        $sut = $this->getSut(session: $sessionStub);

        $this->assertSame($userId, $sut->getPendingUserId());
    }

    #[Test]
    public function isChallengeVerifiedProxiesToTwoFAService(): void
    {
        $twoFAServiceStub = $this->createStub(TwoFAServiceInterface::class);
        $twoFAServiceStub->method('isVerified')
            ->with($userId = uniqid())
            ->willReturn($result = (bool) random_int(0, 1));

        $sut = $this->getSut(twoFAService: $twoFAServiceStub);

        $this->assertSame($result, $sut->isChallengeVerified($userId));
    }

    #[Test]
    public function loginUserLoadsUserLoginsClearsSessionAndRedirects(): void
    {
        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->once())->method('load')->with($userId = uniqid());
        $userSpy->method('getFieldData')->with('oxusername')->willReturn($username = uniqid());
        $userSpy->expects($this->once())->method('login')->with($username, null, false);

        $userFactoryStub = $this->createStub(UserModelFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userSpy);

        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->once())
            ->method('remove')
            ->with(TwoFAUserService::USER_SESSION_KEY);

        $redirectServiceStub = $this->createStub(InternalRedirectServiceInterface::class);
        $redirectServiceStub->method('getRedirectUrl')->willReturn($redirectUrl = uniqid());

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())->method('redirect')->with($redirectUrl, false);

        $sut = $this->getSut(
            userFactory: $userFactoryStub,
            session: $sessionSpy,
            redirectService: $redirectServiceStub,
            utils: $utilsSpy,
        );

        $sut->loginUser($userId);
    }

    private function getSut(
        TwoFAServiceInterface $twoFAService = null,
        TwoFASettingsInterface $settings = null,
        Utils $utils = null,
        SessionInterface $session = null,
        UserModelFactoryInterface $userFactory = null,
        InternalRedirectServiceInterface $redirectService = null,
    ): TwoFAUserService {
        return new TwoFAUserService(
            twoFAService: $twoFAService ?? $this->createStub(TwoFAServiceInterface::class),
            settings: $settings ?? $this->createStub(TwoFASettingsInterface::class),
            utils: $utils ?? $this->createStub(Utils::class),
            session: $session ?? $this->createStub(SessionInterface::class),
            userFactory: $userFactory ?? $this->createStub(UserModelFactoryInterface::class),
            redirectService: $redirectService ?? $this->createStub(InternalRedirectServiceInterface::class),
        );
    }
}
