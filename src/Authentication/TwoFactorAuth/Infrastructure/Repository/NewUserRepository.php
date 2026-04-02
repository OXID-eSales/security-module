<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\NewUserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;

class NewUserRepository implements NewUserRepositoryInterface
{
    public function __construct(
        private UserModelFactoryInterface $userFactory,
        private NewUserFactoryInterface $userDtoFactory,
    ) {
    }

    public function getUserById(string $userId): NewUserInterface
    {
        $userModel = $this->userFactory->create();

        if (!$userModel->load($userId)) {
            throw new UserNotFoundException();
        }

        return $this->userDtoFactory->createFromModel($userModel);
    }
}
