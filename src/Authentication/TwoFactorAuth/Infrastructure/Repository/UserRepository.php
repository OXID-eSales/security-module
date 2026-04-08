<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserModelFactoryInterface $userFactory,
        private UserFactoryInterface $userDtoFactory,
    ) {
    }

    public function getUserById(string $userId): UserInterface
    {
        $userModel = $this->userFactory->create();

        if (!$userModel->load($userId)) {
            throw new UserNotFoundException();
        }

        return $this->userDtoFactory->createFromModel($userModel);
    }
}
