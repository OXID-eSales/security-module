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
use OxidEsales\SecurityModule\FormSecurity\Service\SessionlessHiddenParamsBuilderInterface;
// phpcs:ignore Generic.Files.LineLength
use OxidEsales\SecurityModule\FormSecurity\Service\ModuleSettingsServiceInterface as FormSecuritySettingsServiceInterface;
use OxidEsales\SecurityModule\Shared\Core\ViewConfig;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[AllowMockObjectsWithoutExpectations]
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

    #[Test]
    #[DataProvider('isGetFormStripStokenEnabledDataProvider')]
    public function isGetFormStripStokenEnabledDelegatesToFormSecuritySettings(bool $enabled): void
    {
        $formSettingsStub = $this->createStub(FormSecuritySettingsServiceInterface::class);
        $formSettingsStub->method('isGetFormStripStokenEnabled')->willReturn($enabled);

        $sut = $this->getSut([
            FormSecuritySettingsServiceInterface::class => $formSettingsStub,
        ]);

        $this->assertSame($enabled, $sut->isGetFormStripStokenEnabled());
    }

    public static function isGetFormStripStokenEnabledDataProvider(): Generator
    {
        yield 'form security enabled' => ['enabled' => true];
        yield 'form security disabled' => ['enabled' => false];
    }

    #[Test]
    public function getHiddenParamsOnlyDelegatesToSessionlessHiddenParamsBuilderWithAdditionalRequestParameters(): void
    {
        $additionalParams = '<input type="hidden" name="cnid" value="abc">';
        $expected = '<input type="hidden" name="lang" value="0">' . $additionalParams;

        $builderMock = $this->createMock(SessionlessHiddenParamsBuilderInterface::class);
        $builderMock->expects($this->once())
            ->method('build')
            ->with($additionalParams)
            ->willReturn($expected);

        $sut = $this->getSut([
            SessionlessHiddenParamsBuilderInterface::class => $builderMock,
        ]);
        $sut->method('getAdditionalRequestParameters')->willReturn($additionalParams);

        $this->assertSame($expected, $sut->getHiddenParamsOnly());
    }

    private function getSut(array $serviceOverrides = []): ViewConfig
    {
        /** @var ViewConfig $sut */
        $sut = $this->getMockBuilder(ViewConfig::class)
            ->onlyMethods(['getService', 'getAdditionalRequestParameters'])
            ->getMock();
        $sut->method('getService')->willReturnCallback(
            fn(string $id) => $serviceOverrides[$id] ?? ContainerFacade::get($id)
        );

        return $sut;
    }
}
