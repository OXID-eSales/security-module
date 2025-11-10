<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure;

interface UserRepositoryInterface
{
    public function getUserByEmail(string $username): string|bool;
}
