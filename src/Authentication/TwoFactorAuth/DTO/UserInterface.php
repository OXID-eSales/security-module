<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO;

interface UserInterface
{
    public function getUserId(): string;

    public function getEmail(): string;

    public function isTwoFAEnabled(): bool;
}
