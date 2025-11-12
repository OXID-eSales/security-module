<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\DTO;

class UserDTO implements UserDTOInterface
{
    public function __construct(
        private readonly ?string $firstName,
        private readonly ?string $lastName,
        private readonly ?string $email,
    ) {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
