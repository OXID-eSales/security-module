<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction;

use OxidEsales\Eshop\Application\Model\User as EshopUser;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\Shared\Controller\RegisterController as ModuleRegisterController;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class RegisterConfirmRegistrationTokenTest extends IntegrationTestCase
{
    #[Test]
    public function featureOffDoesNotSetLoginToken(): void
    {
        $user = $this->createMock(EshopUser::class);
        $user->expects($this->never())->method('setSessionLoginToken');

        $subject = $this->getSut(false, $user);
        $subject->confirmRegistration();
    }

    #[Test]
    public function featureOnWithLoggedInUserSetsLoginToken(): void
    {
        $user = $this->createMock(EshopUser::class);
        $user->expects($this->once())->method('setSessionLoginToken');

        $subject = $this->getSut(true, $user);
        $subject->confirmRegistration();
    }

    #[Test]
    public function featureOnWithNoLoggedInUserDoesNothing(): void
    {
        $subject = $this->getSut(true, false);

        $this->assertSame('account', $subject->confirmRegistration());
    }

    private function getSut(bool $enabled, EshopUser|false $user): ModuleRegisterController
    {
        $services = [
            ModuleSettingsServiceInterface::class => $this->createConfiguredStub(
                ModuleSettingsServiceInterface::class,
                ['isEnabled' => $enabled]
            ),
        ];

        /** @var ModuleRegisterController $sut */
        $sut = $this->getMockBuilder(ModuleRegisterController::class)
            ->onlyMethods(['getService', 'getUser'])
            ->getMock();
        $sut->method('getService')->with($this->anything())->willReturnCallback(
            fn(string $id) => $services[$id] ?? ContainerFacade::get($id)
        );
        $sut->method('getUser')->willReturn($user);

        return $sut;
    }
}
