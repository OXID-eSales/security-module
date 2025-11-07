<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\DataType;

interface UserDataTypeInterface
{
    public function getFirstName(): string;

    public function getLastName(): string;

    public function getEmail(): string;
}
