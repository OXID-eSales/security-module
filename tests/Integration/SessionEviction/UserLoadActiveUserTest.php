<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\SessionEviction;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\SecurityModule\SessionEviction\Service\SessionEvictionServiceInterface;
use OxidEsales\SecurityModule\Shared\Model\User as SecurityModuleUser;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class UserLoadActiveUserTest extends IntegrationTestCase
{
    #[Test]
    public function loadActiveUserDelegatesToSessionEvictionServiceAroundParent(): void
    {
        $service = $this->createMock(SessionEvictionServiceInterface::class);
        $service->expects($this->once())
            ->method('evictUnverifiableSession')
            ->with($this->isInstanceOf(SecurityModuleUser::class), false);
        $service->expects($this->once())
            ->method('bindLoginTokenToSession')
            ->with($this->isInstanceOf(SecurityModuleUser::class), $this->isBool());

        $this->getSut([SessionEvictionServiceInterface::class => $service])->loadActiveUser();
    }

    #[Test]
    public function loadActiveUserForwardsTheForceAdminFlag(): void
    {
        $service = $this->createMock(SessionEvictionServiceInterface::class);
        $service->expects($this->once())
            ->method('evictUnverifiableSession')
            ->with($this->isInstanceOf(SecurityModuleUser::class), true);

        $this->getSut([SessionEvictionServiceInterface::class => $service])->loadActiveUser(true);
    }

    private function getSut(array $serviceOverrides = []): SecurityModuleUser
    {
        /** @var SecurityModuleUser $sut */
        $sut = $this->getMockBuilder(SecurityModuleUser::class)
            ->onlyMethods(['getService'])
            ->getMock();
        $sut->expects($this->atLeastOnce())->method('getService')->willReturnCallback(
            fn(string $id) => $serviceOverrides[$id] ?? ContainerFacade::get($id)
        );

        return $sut;
    }
}
