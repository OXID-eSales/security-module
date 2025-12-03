<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\DTO;

interface UserDTOInterface
{
    public function getId(): string;

    public function isBlocked(): bool;
}
