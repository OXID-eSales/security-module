<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\NewUserFactory;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\NewUserFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NewUserFactoryTest extends TestCase
{
    #[Test]
    public function createFromModelMapsIdAndEmail(): void
    {
        $userModelStub = $this->createStub(User::class);
        $userModelStub->method('getId')->willReturn($userId = uniqid());
        $userModelStub->method('getFieldData')->with('oxusername')->willReturn($email = uniqid());

        $sut = $this->getSut();

        $result = $sut->createFromModel(userModel: $userModelStub);

        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($email, $result->getEmail());
    }

    #[Test]
    public function implementsInterface(): void
    {
        $this->assertInstanceOf(NewUserFactoryInterface::class, $this->getSut());
    }

    private function getSut(): NewUserFactory
    {
        return new NewUserFactory();
    }
}
