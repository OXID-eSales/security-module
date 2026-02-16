<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserBlockedException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Repository\UserRepositoryInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SessionInterface $session,
    ) {
    }

    public function login(OAuth2UserDTOInterface $auth2UserDTO): void
    {
        if (!$auth2UserDTO->getEmail()) {
            throw new UserNotFoundException();
        }

        try {
            $userDTO = $this->userRepository->getUserByEmail($auth2UserDTO->getEmail());
            if ($userDTO->isBlocked()) {
                throw new UserBlockedException();
            }
        } catch (UserNotFoundException $e) {
            $userDTO = $this->userRepository->createUser($auth2UserDTO);
        }

        $this->session->set('usr', $userDTO->getId());
    }

    public function removeExternalAuthFlag(): void
    {
        $userId = $this->session->get('usr');
        if ($userId) {
            $this->userRepository->removeExternalAuthFlag((string)$userId);
        }
    }
}
