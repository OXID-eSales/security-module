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

final class UserSaveTokenRefreshTest extends IntegrationTestCase
{
    private const USER_ID = 'testuser';

    #[Test]
    public function saveCapturesThePriorPasswordHashAndDelegatesTheTokenRefresh(): void
    {
        $priorHash = uniqid('hash-', true);

        $service = $this->createMock(SessionEvictionServiceInterface::class);
        $service->expects($this->once())
            ->method('readCurrentPasswordHash')
            ->with($this->isInstanceOf(SecurityModuleUser::class))
            ->willReturn($priorHash);
        $service->expects($this->once())
            ->method('refreshTokenAfterPasswordChange')
            ->with($this->isInstanceOf(SecurityModuleUser::class), $priorHash);

        $sut = $this->getSut([SessionEvictionServiceInterface::class => $service]);
        $sut->load(self::USER_ID);

        $this->assertNotEmpty($sut->save());
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
