<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataType\UserDataTypeInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserBlockedException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Factory\UserFactoryInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private SessionInterface $session,
    ) {
    }

    public function login(UserDataTypeInterface $userDataType): void
    {
        try {
            $userModel = $this->getUserByUserEmail($userDataType->getEmail());
        } catch (UserNotFoundException $e) {
            $userModel = $this->createUser($userDataType);
        }

        if ($userModel->inGroup('oxidblocked')) {
            throw new UserBlockedException();
        }

        $this->session->set('usr', $userModel->getId());
    }

    public function getUserByUserEmail(string $username): User|bool
    {
        $userModel = $this->userFactory->create();

        $userId = $userModel->getIdByUserName($username);
        $userLoaded = $userModel->load($userId);
        if (!$userLoaded) {
            throw new UserNotFoundException();
        }

        return $userModel;
    }

    public function createUser(userDataTypeInterface $userDataType): User
    {
        $user = $this->userFactory->create();
        $user->assign([
            'OXUSERNAME' => $userDataType->getEmail(),
            'OXFNAME'    => $userDataType->getFirstName(),
            'OXLNAME'    => $userDataType->getLastName(),
            'OXREGISTER' => time()
        ]);
        $user->setPassword(bin2hex(random_bytes(20)));
        $user->createUser();

        return $user;
    }
}
