<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;

interface UserDTOFactoryInterface
{
    public function createFromModel(UserModel $userModel): UserDTOInterface;
}
