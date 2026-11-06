<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Shared\Controller\ForgotPasswordController as ModuleForgotPasswordController;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class ForgotPasswordUpdatePasswordTokenTest extends IntegrationTestCase
{
    protected UtilsView $utilsViewStub;
    protected Request $requestStub;

    public function setUp(): void
    {
        parent::setUp();

        $this->utilsViewStub = $this->createStub(UtilsView::class);

        $this->requestStub = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestParameter'])
            ->getMock();
        $this->requestStub->method('getRequestParameter')->withAnyParameters()->willReturn('');

        Registry::set(UtilsView::class, $this->utilsViewStub);
        Registry::set(Request::class, $this->requestStub);
    }

    #[Test]
    public function featureOffDoesNotSetLoginToken(): void
    {
        $user = $this->createMock(EshopUser::class);
        $user->expects($this->never())->method('setSessionLoginToken');

        $subject = $this->getSut(false, $user);
        $subject->updatePassword();
    }

    #[Test]
    public function featureOnWithLoggedInUserSetsLoginToken(): void
    {
        $user = $this->createMock(EshopUser::class);
        $user->expects($this->once())->method('setSessionLoginToken');

        $subject = $this->getSut(true, $user);
        $subject->updatePassword();
    }

    #[Test]
    public function featureOnWithNoLoggedInUserDoesNothing(): void
    {
        $subject = $this->getSut(true, false);

        $this->assertNull($subject->updatePassword());
    }

    private function getSut(bool $enabled, EshopUser|false $user): ModuleForgotPasswordController
    {
        $services = [
            ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                ModuleSettingsServiceInterface::class,
                ['isEnabled' => $enabled]
            ),
        ];

        /** @var ModuleForgotPasswordController $sut */
        $sut = $this->getMockBuilder(ModuleForgotPasswordController::class)
            ->onlyMethods(['getService', 'getUser'])
            ->getMock();
        $sut->method('getService')->with($this->anything())->willReturnCallback(
            fn(string $id) => $services[$id] ?? ContainerFacade::get($id)
        );
        $sut->method('getUser')->willReturn($user);

        return $sut;
    }
}
