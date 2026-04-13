<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\TwoFactorAuth\Controller;

use Generator;
use OxidEsales\Eshop\Application\Controller\AccountController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller\AccountSecurityController;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAUserSettingsInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class AccountSecurityControllerTest extends IntegrationTestCase
{
    #[Test]
    public function extendsAccountController(): void
    {
        $this->assertInstanceOf(AccountController::class, $this->getSut());
    }

    #[Test]
    #[DataProvider('renderSetsTwoFAEnabledDataProvider')]
    public function renderSetsTwoFAEnabled(bool $userSettingEnabled): void
    {
        $userId = uniqid();

        $userStub = $this->createStub(User::class);
        $userStub->method('getId')->willReturn($userId);

        $userSettingsStub = $this->createStub(TwoFAUserSettingsInterface::class);
        $userSettingsStub->method('isEnabledForUser')->with($userId)->willReturn($userSettingEnabled);

        $sut = $this->getSut(userSettingsService: $userSettingsStub);
        $sut->method('getUser')->willReturn($userStub);
        $sut->render();

        $this->assertSame($userSettingEnabled, $sut->getViewDataElement('twoFAEnabledForUser'));
    }

    public static function renderSetsTwoFAEnabledDataProvider(): Generator
    {
        yield 'user has 2FA enabled' => ['userSettingEnabled' => true];
        yield 'user has 2FA disabled' => ['userSettingEnabled' => false];
    }

    private function getSut(
        TwoFAUserSettingsInterface $userSettingsService = null,
    ): AccountSecurityController {
        return $this->getMockBuilder(AccountSecurityController::class)
            ->setConstructorArgs([
                'userSettingsService' => $userSettingsService ?? $this->createStub(TwoFAUserSettingsInterface::class),
            ])
            ->onlyMethods(['getUser'])
            ->getMock();
    }
}
