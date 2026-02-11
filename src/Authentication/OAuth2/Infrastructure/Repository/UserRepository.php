<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Repository;

use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\UserDTOFactoryInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Shared\Service\PasswordGeneratorServiceInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private UserDTOFactoryInterface $userDTOFactory,
        private PasswordGeneratorServiceInterface $passwordGenerator
    ) {
    }

    public function getUserByEmail(string $username): UserDTOInterface
    {
        $userModel = $this->userFactory->create();

        $userId = $userModel->getIdByUserName($username);
        if (!$userId || !$userModel->load($userId)) {
            throw new UserNotFoundException();
        }

        return $this->userDTOFactory->createFromModel($userModel);
    }

    public function createUser(OAuth2UserDTOInterface $userDTO): UserDTOInterface
    {
        $userModel = $this->userFactory->create();
        $userModel->assign([
            'OXFNAME'          => $userDTO->getFirstName(),
            'OXLNAME'          => $userDTO->getLastName(),
            'OXUSERNAME'       => $userDTO->getEmail(),
            'OESMEXTERNALAUTH' => 1,
        ]);
        $userModel->setPassword($this->passwordGenerator->generatePasswordForOAuthUser());
        $userModel->createUser();

        return $this->userDTOFactory->createFromModel($userModel);
    }
}
