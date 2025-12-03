<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\OAuth2\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\UserDTOFactory;
use PHPUnit\Framework\TestCase;

class UserDTOFactoryTest extends TestCase
{
    public function testCreateFromModel(): void
    {
        $userId = uniqid();
        $isBlocked = (bool)rand(0, 1);

        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock->method('getId')->willReturn($userId);
        $userModelMock->method('inGroup')->with('oxidblocked')->willReturn($isBlocked);

        $sut = new UserDTOFactory();

        $userDTO = $sut->createFromModel($userModelMock);

        $this->assertSame($userId, $userDTO->getId());
        $this->assertSame($isBlocked, $userDTO->isBlocked());
    }
}
