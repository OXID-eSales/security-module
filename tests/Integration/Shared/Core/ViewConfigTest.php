<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Shared\Core;

use Generator;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettingsInterface;
use OxidEsales\SecurityModule\Shared\Core\ViewConfig;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ViewConfigTest extends IntegrationTestCase
{
    #[DataProvider('isTwoFAEnabledForShopDataProvider')]
    public function testIsTwoFAEnabledForShop(bool $shopSettingEnabled): void
    {
        $shopSettingsStub = $this->createStub(TwoFAShopSettingsInterface::class);
        $shopSettingsStub->method('isTwoFactorAuthEnabled')->willReturn($shopSettingEnabled);

        $sut = $this->getSut([
            TwoFAShopSettingsInterface::class => $shopSettingsStub,
        ]);

        $this->assertSame($shopSettingEnabled, $sut->isTwoFAEnabledForShop());
    }

    public static function isTwoFAEnabledForShopDataProvider(): Generator
    {
        yield 'shop setting enabled' => ['shopSettingEnabled' => true];
        yield 'shop setting disabled' => ['shopSettingEnabled' => false];
    }

    private function getSut(array $serviceOverrides = []): ViewConfig
    {
        /** @var ViewConfig $sut */
        $sut = $this->getMockBuilder(ViewConfig::class)
            ->onlyMethods(['getService'])
            ->getMock();
        $sut->method('getService')->willReturnCallback(
            fn(string $id) => $serviceOverrides[$id] ?? ContainerFacade::get($id)
        );

        return $sut;
    }
}
