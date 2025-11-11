<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataType\UserDataTypeInterface;

interface UserServiceInterface
{
    public function login(UserDataTypeInterface $userDataType): void;

    public function getUserByUserEmail(string $username): User|bool;

    public function createUser(UserDataTypeInterface $userDataType): User;
}
