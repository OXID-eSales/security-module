<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserBlockedException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Repository\UserRepositoryInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SessionInterface $session,
    ) {
    }

    public function login(UserInterface $userDataObject): void
    {
        if (!$userDataObject->getEmail()) {
            throw new UserNotFoundException();
        }

        try {
            $userModel = $this->userRepository->getUserByUserEmail($userDataObject->getEmail());

            if ($userModel->inGroup('oxidblocked')) {
                throw new UserBlockedException();
            }
        } catch (UserNotFoundException $e) {
            $userModel = $this->userRepository->createUser($userDataObject);
        }

        $this->session->set('usr', $userModel->getId());
    }
}
