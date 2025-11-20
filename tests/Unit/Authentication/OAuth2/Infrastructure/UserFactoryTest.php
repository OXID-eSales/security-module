<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\OAuth2\Infrastructure;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\UserFactory;
use PHPUnit\Framework\TestCase;

class UserFactoryTest extends TestCase
{
    public function testCreateUserFactory(): void
    {
        $userFactory = new UserFactory();
        $userModel = $userFactory->create();

        $this->assertInstanceOf(User::class, $userModel);
    }

    public function testCreateMultipleUserFactory(): void
    {
        $userFactory = new UserFactory();

        $userModel = $userFactory->create();
        $this->assertInstanceOf(User::class, $userModel);

        $newUserFactory = $userFactory->create();
        $this->assertInstanceOf(User::class, $newUserFactory);

        $this->assertNotSame($userModel, $newUserFactory);
    }
}
