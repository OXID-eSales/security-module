<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\PasswordReuse\Infrastructure\Repository;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;
use OxidEsales\SecurityModule\PasswordReuse\DTO\AccountData;
use OxidEsales\SecurityModule\PasswordReuse\DTO\AccountDataInterface;
use OxidEsales\SecurityModule\PasswordReuse\Exception\AccountNotFoundException;

class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        private UserModelFactoryInterface $userModelFactory,
    ) {
    }

    public function getById(string $userId): AccountDataInterface
    {
        $userModel = $this->userModelFactory->create();

        if (!$userModel->load($userId)) {
            throw new AccountNotFoundException();
        }

        return new AccountData(
            userId: (string)$userModel->getId(),
            email: (string)$userModel->getFieldData('oxusername'),
            rights: (string)$userModel->getFieldData('oxrights'),
        );
    }
}
