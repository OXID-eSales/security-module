<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User as UserDto;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;

class UserFactory implements UserFactoryInterface
{
    public function createFromModel(User $userModel): UserInterface
    {
        return new UserDto(
            userId: $userModel->getId(),
            email: $userModel->getFieldData('oxusername'),
        );
    }
}
