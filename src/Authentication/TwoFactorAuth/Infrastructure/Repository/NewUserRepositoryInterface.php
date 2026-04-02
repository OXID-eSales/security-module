<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\NewUserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;

interface NewUserRepositoryInterface
{
    /** @throws UserNotFoundException */
    public function getUserById(string $userId): NewUserInterface;
}
