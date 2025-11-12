<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;
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

    public function login(UserDTOInterface $userDTO): void
    {
        if (!$userDTO->getEmail()) {
            throw new UserNotFoundException();
        }

        try {
            $userModel = $this->getUserByUserEmail($userDTO->getEmail());
        } catch (UserNotFoundException $e) {
            $userModel = $this->createUser($userDTO);
        }

        if ($userModel->inGroup('oxidblocked')) {
            throw new UserBlockedException();
        }

        $this->session->set('usr', $userModel->getId());
    }

    public function getUserByUserEmail(string $username): User
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

    public function createUser(UserDTOInterface $userDTO): User
    {
        $user = $this->userFactory->create();
        $user->assign([
            'OXUSERNAME' => $userDTO->getEmail(),
            'OXFNAME'    => $userDTO->getFirstName(),
            'OXLNAME'    => $userDTO->getLastName(),
            'OXREGISTER' => date('Y-m-d H:i:s')
        ]);
        $user->setPassword(bin2hex(random_bytes(20)));
        $user->createUser();

        return $user;
    }
}
