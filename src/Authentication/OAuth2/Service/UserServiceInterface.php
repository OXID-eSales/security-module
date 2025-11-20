<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Service;

use OxidEsales\SecurityModule\Authentication\OAuth2\DataObject\UserInterface;

interface UserServiceInterface
{
    public function login(UserInterface $userDataObject): void;
}
