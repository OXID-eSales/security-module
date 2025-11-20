<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Repository;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;

interface UserRepositoryInterface
{
    public function getUserByUserEmail(string $username): UserModel;

    public function createUser(UserInterface $userDataObject): UserModel;
}
