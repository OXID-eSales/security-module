<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Repository;

use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\UserFactoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory
    ) {
    }

    public function getUserByUserEmail(string $username): UserModel
    {
        $userModel = $this->userFactory->create();

        $userId = $userModel->getIdByUserName($username);
        if (
            !$userId ||
            !$userModel->load($userId)
        ) {
            throw new UserNotFoundException();
        }

        return $userModel;
    }

    public function createUser(UserInterface $userDataObject): UserModel
    {
        $user = $this->userFactory->create();
        $user->assign([
            'OXUSERNAME' => $userDataObject->getEmail(),
            'OXREGISTER' => date('Y-m-d H:i:s')
        ]);
        $user->setPassword(bin2hex(random_bytes(20)));
        $user->createUser();

        return $user;
    }
}
