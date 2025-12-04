<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DataObject\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
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

    public function getUserPasswordHash(string $userName): string
    {
        $qb = $this->queryBuilderFactory->create();
        $qb->select('OXPASSWORD')
            ->from('oxuser')
            ->where('oxusername = :userName')
            ->setParameter('userName', $userName);

        return $qb->execute()->fetchOne();
    }
}
