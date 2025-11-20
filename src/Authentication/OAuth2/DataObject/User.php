<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\DataObject;

readonly class User implements UserInterface
{
    public function __construct(
        private ?string $email,
    ) {
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
