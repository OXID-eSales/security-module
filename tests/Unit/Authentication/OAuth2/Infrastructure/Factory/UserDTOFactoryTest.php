<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTO;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\UserDTOFactory;
use PHPUnit\Framework\TestCase;

class UserDTOFactoryTest extends TestCase
{
    public function testCreateFromModel(): void
    {
        $userId = uniqid();
        $isBlocked = true;

        $userModelMock = $this->createMock(UserModel::class);
        $userModelMock->method('getId')->willReturn($userId);
        $userModelMock->method('inGroup')->with('oxidblocked')->willReturn($isBlocked);

        $factory = new UserDTOFactory();

        $userDTO = $factory->createFromModel($userModelMock);

        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertSame($userId, $userDTO->getId());
        $this->assertSame($isBlocked, $userDTO->isBlocked());
    }
}
