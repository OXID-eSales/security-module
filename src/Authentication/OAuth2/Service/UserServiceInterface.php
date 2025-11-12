<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;

interface UserServiceInterface
{
    public function login(UserDTOInterface $userDTO): void;

    public function getUserByUserEmail(string $username): User;

    public function createUser(UserDTOInterface $userDTO): User;
}
