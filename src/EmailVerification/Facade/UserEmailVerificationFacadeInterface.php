<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Facade;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\SecurityModule\EmailVerification\Exception\EmailNotVerifiedException;

interface UserEmailVerificationFacadeInterface
{
    public function isEmailVerificationRequired(string $userId): bool;

    public function initializeEmailVerification(User $user): bool;

    public function rememberPendingUser(string $userId): void;

    /**
     * @throws EmailNotVerifiedException
     */
    public function stopUnverifiedLogin(string $userId): void;
}
