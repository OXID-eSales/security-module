<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTO;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;

class UserDTOFactory implements UserDTOFactoryInterface
{
    public function createFromModel(UserModel $userModel): UserDTOInterface
    {
        return new UserDTO(
            $userModel->getId(),
            $userModel->inGroup('oxidblocked'),
        );
    }
}
