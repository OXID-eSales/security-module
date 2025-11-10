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
use OxidEsales\SecurityModule\Authentication\OAuth2\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\UserRepositoryInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private SessionInterface $session,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function login(UserDataTypeInterface $userDataType): void
    {
        $userModel = $this->getUserByUserId($userDataType->getEmail());
        if (!$userModel instanceof User) {
            $userModel = $this->createUser($userDataType);
        }

        if ($userModel->inGroup('oxidblocked')) {
            throw new \Exception('Blocked');
        }

        $this->session->set('usr', $userModel->getId());
    }

    public function getUserByUserId(string $username): User|bool
    {
        $userId = $this->userRepository->getUserByEmail($username);
        if ($userId) {
            $user = $this->userFactory->create();
            $user->load($userId);

            return $user;
        }

        return false;
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
