<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\TwoFactorRequiredException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Service\UserLoginAdapterInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAUserSettingsInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserService;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettingsInterface;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TwoFAUserServiceTest extends TestCase
{
    #[Test]
    public function startChallengeForUserStoresPendingUserTriggersChallengeLogsAndThrows(): void
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

        $settingsStub = $this->createStub(TwoFAShopSettingsInterface::class);

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->never())->method('redirect');

        $loggerSpy = $this->createMock(LoggerInterface::class);
        $loggerSpy->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('two-factor'),
                $this->callback(fn(array $context) => ($context['userId'] ?? null) === $userId)
            );

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            settings: $settingsStub,
            utils: $utilsSpy,
            session: $sessionSpy,
            logger: $loggerSpy,
        );

        $this->expectExceptionObject(new TwoFactorRequiredException($userId, uniqid()));

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
    #[DataProvider('isTwoFARequiredDataProvider')]
    public function isTwoFARequired(bool $shopSettingEnabled, bool $userSettingEnabled, bool $expected): void
    {
        $shopSettingsStub = $this->createStub(TwoFAShopSettingsInterface::class);
        $shopSettingsStub->method('isTwoFactorAuthEnabled')->willReturn($shopSettingEnabled);

        $userSettingsStub = $this->createStub(TwoFAUserSettingsInterface::class);
        $userSettingsStub->method('isEnabledForUser')->willReturn($userSettingEnabled);

        $sut = $this->getSut(settings: $shopSettingsStub, userSettings: $userSettingsStub);

        $this->assertSame($expected, $sut->isTwoFARequired(uniqid()));
    }

    public static function isTwoFARequiredDataProvider(): Generator
    {
        yield 'shop setting disabled, user has it disabled' => [
            'shopSettingEnabled' => false,
            'userSettingEnabled' => false,
            'expected' => false,
        ];
        yield 'shop setting disabled, user has it enabled' => [
            'shopSettingEnabled' => false,
            'userSettingEnabled' => true,
            'expected' => false,
        ];
        yield 'shop setting enabled, user has it disabled' => [
            'shopSettingEnabled' => true,
            'userSettingEnabled' => false,
            'expected' => false,
        ];
        yield 'shop setting enabled, user has it enabled' => [
            'shopSettingEnabled' => true,
            'userSettingEnabled' => true,
            'expected' => true,
        ];
    }

    #[Test]
    public function abandonChallengeClearsSessionInvalidatesChallengeAndRedirectsToAccount(): void
    {
        $userId = uniqid();

        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->once())
            ->method('remove')
            ->with(TwoFAUserService::USER_SESSION_KEY);

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->once())
            ->method('invalidateChallenge')
            ->with($userId);

        $settingsStub = $this->createStub(TwoFAShopSettingsInterface::class);
        $settingsStub->method('getAccountUrl')->willReturn(uniqid());

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())
            ->method('redirect');

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            settings: $settingsStub,
            utils: $utilsSpy,
            session: $sessionSpy,
        );

        $sut->abandonChallenge($userId);
    }

    #[Test]
    public function loginUserLoadsUserLoginsClearsSessionAndRedirects(): void
    {
        $userId = uniqid();

        $loginAdapterSpy = $this->createMock(UserLoginAdapterInterface::class);
        $loginAdapterSpy->expects($this->once())
            ->method('loginUser')
            ->with($userId);

        $twoFAServiceSpy = $this->createMock(TwoFAServiceInterface::class);
        $twoFAServiceSpy->expects($this->once())
            ->method('invalidateChallenge')
            ->with($userId);

        $sessionSpy = $this->createMock(SessionInterface::class);
        $sessionSpy->expects($this->once())
            ->method('remove')
            ->with(TwoFAUserService::USER_SESSION_KEY);

        $redirectServiceStub = $this->createStub(InternalRedirectServiceInterface::class);
        $redirectServiceStub->method('getRedirectUrl')->willReturn($redirectUrl = uniqid());

        $utilsSpy = $this->createMock(Utils::class);
        $utilsSpy->expects($this->once())->method('redirect')->with($redirectUrl, false);

        $sut = $this->getSut(
            twoFAService: $twoFAServiceSpy,
            loginAdapter: $loginAdapterSpy,
            session: $sessionSpy,
            redirectService: $redirectServiceStub,
            utils: $utilsSpy,
        );

        $sut->loginUser($userId);
    }

    private function getSut(
        TwoFAServiceInterface $twoFAService = null,
        TwoFAShopSettingsInterface $settings = null,
        Utils $utils = null,
        SessionInterface $session = null,
        UserLoginAdapterInterface $loginAdapter = null,
        InternalRedirectServiceInterface $redirectService = null,
        TwoFAUserSettingsInterface $userSettings = null,
        LoggerInterface $logger = null,
    ): TwoFAUserService {
        return new TwoFAUserService(
            twoFAService: $twoFAService ?? $this->createStub(TwoFAServiceInterface::class),
            settings: $settings ?? $this->createStub(TwoFAShopSettingsInterface::class),
            utils: $utils ?? $this->createStub(Utils::class),
            session: $session ?? $this->createStub(SessionInterface::class),
            loginAdapter: $loginAdapter ?? $this->createStub(UserLoginAdapterInterface::class),
            redirectService: $redirectService ?? $this->createStub(InternalRedirectServiceInterface::class),
            userSettings: $userSettings ?? $this->createStub(TwoFAUserSettingsInterface::class),
            logger: $logger ?? $this->createStub(LoggerInterface::class),
        );
    }
}
