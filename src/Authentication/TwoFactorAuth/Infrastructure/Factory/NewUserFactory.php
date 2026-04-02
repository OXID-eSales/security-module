<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUser;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUserInterface;

class NewUserFactory implements NewUserFactoryInterface
{
    public function createFromModel(User $userModel): NewUserInterface
    {
        return new NewUser(
            userId: $userModel->getId(),
            email: $userModel->getFieldData('oxusername'),
        );
    }
}
