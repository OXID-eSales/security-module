<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory
    ) {
    }

    public function addOTPtoUser(string $userId, string $otp, int $expiresAt): bool
    {
        $userModel = $this->userFactory->create();
        $userModel->load($userId);
        $userModel->assign([
            'OESMOTPCODE'       => $otp,
            'OESMOTPEXPTIME' => $expiresAt,
            'OESMOTPATTEMPTS'   => 0,
        ]);
        $userModel->save();

        return true;
    }

    public function updateAttempts(string $userId, int $attempts): int
    {
        $userModel = $this->userFactory->create();
        $userModel->load($userId);
        $userModel->assign([
            'OESMOTPATTEMPTS' => $attempts
        ]);
        $userModel->save();
    }

    public function resetCodeFields(string $userId): void
    {
        $userModel = $this->userFactory->create();
        $userModel->load($userId);
        $userModel->assign([
            'OESMOTPCODE'       => '',
            'OESMOTPEXPTIME' => 0,
            'OESMOTPATTEMPTS'   => 0,
        ]);
        $userModel->save();
    }
}
