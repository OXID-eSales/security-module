<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Authentication\TwoFactorAuth\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactory;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserFactoryTest extends TestCase
{
    #[Test]
    public function createFromModelMapsAllProperties(): void
    {
        $twoFAEnabledRaw = random_int(0, 1);

        $userModelStub = $this->createStub(User::class);
        $userModelStub->method('getId')->willReturn($userId = uniqid());
        $userModelStub->method('getFieldData')
            ->willReturnMap([
                ['oxusername', $email = uniqid()],
                ['oe2faenabled', $twoFAEnabledRaw],
            ]);

        $result = $this->getSut()->createFromModel(userModel: $userModelStub);

        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($email, $result->getEmail());
        $this->assertSame((bool) $twoFAEnabledRaw, $result->isTwoFAEnabled());
    }

    #[Test]
    public function implementsInterface(): void
    {
        $this->assertInstanceOf(UserFactoryInterface::class, $this->getSut());
    }

    private function getSut(): UserFactory
    {
        return new UserFactory();
    }
}
