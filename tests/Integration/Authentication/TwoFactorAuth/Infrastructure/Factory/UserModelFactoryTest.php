<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\TwoFactorAuth\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactory;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserModelFactoryTest extends TestCase
{
    #[Test]
    public function createReturnsUserModel(): void
    {
        $sut = $this->getSut();

        $this->assertInstanceOf(User::class, $sut->create());
    }

    #[Test]
    public function createReturnsNewInstanceOnEachCall(): void
    {
        $sut = $this->getSut();

        $this->assertNotSame($sut->create(), $sut->create());
    }

    private function getSut(): UserModelFactoryInterface
    {
        return new UserModelFactory();
    }
}
