<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Service;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;

class UserLoginAdapter implements UserLoginAdapterInterface
{
    public function __construct(
        private UserModelFactoryInterface $userFactory,
    ) {
    }

    public function loginUser(string $userId): void
    {
        $user = $this->userFactory->create();
        $user->load($userId);
        /** @phpstan-ignore argument.type (password is null because user already authenticated to come here) */
        $user->login($user->getFieldData('oxusername'), null, false);
    }
}
