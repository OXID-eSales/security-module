<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Infrastructure\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Service\UserLoginAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserLoginAdapterTest extends TestCase
{
    #[Test]
    public function loginUserLoadsAndLoginsUser(): void
    {
        $userSpy = $this->createMock(User::class);
        $userSpy->expects($this->once())->method('load')->with($userId = uniqid());
        $userSpy->method('getFieldData')->with('oxusername')->willReturn($username = uniqid());
        $userSpy->expects($this->once())->method('login')->with($username, null, false);

        $userFactoryStub = $this->createStub(UserModelFactoryInterface::class);
        $userFactoryStub->method('create')->willReturn($userSpy);

        $sut = $this->getSut(userFactory: $userFactoryStub);

        $sut->loginUser($userId);
    }

    private function getSut(
        UserModelFactoryInterface $userFactory = null,
    ): UserLoginAdapter {
        return new UserLoginAdapter(
            userFactory: $userFactory ?? $this->createStub(UserModelFactoryInterface::class),
        );
    }
}
