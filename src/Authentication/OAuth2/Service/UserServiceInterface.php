<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserBlockedException;
use OxidEsales\SecurityModule\Authentication\OAuth2\Exception\UserNotFoundException;

interface UserServiceInterface
{
    /**
     * @throws UserNotFoundException If the user is not found.
     * @throws UserBlockedException If the user is blocked.
     */
    public function login(OAuth2UserDTOInterface $auth2UserDTO): void;

    public function removeExternalAuthFlag(): void;
}
