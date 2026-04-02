<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Integration\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\NewUserRepositoryInterface;
use OxidEsales\SecurityModule\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

class NewUserRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function getUserByIdThrowsWhenUserNotFound(): void
    {
        $sut = $this->getSut();

        $this->expectException(UserNotFoundException::class);

        $sut->getUserById(userId: uniqid());
    }

    #[Test]
    public function getUserByIdReturnsUserDto(): void
    {
        $userModel = oxNew(User::class);
        $userModel->setId($userId = uniqid());
        $userModel->assign([
            'oxactive'   => 1,
            'oxusername' => $email = uniqid() . '@example.com',
        ]);
        $userModel->save();

        $sut = $this->getSut();

        $result = $sut->getUserById(userId: $userId);

        $this->assertInstanceOf(NewUserInterface::class, $result);
        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($email, $result->getEmail());
    }

    private function getSut(): NewUserRepositoryInterface
    {
        return $this->get(NewUserRepositoryInterface::class);
    }
}
