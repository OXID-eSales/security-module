<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use DateTime;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User as UserDTO;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserFactoryInterface $userFactory,
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {
    }

    public function getUserOTPData(string $userName): UserDTO
    {
        //todo: exception if not found
        $builder = $this->queryBuilderFactory->create();
        $builder->select([
                'OXID',
                'OESMOTPCODE',
                'OESMOTPATTEMPTS',
                'OESMOTPEXPTIME'
            ])
            ->from('oxuser')
            ->where('oxusername = :userName')
            ->setParameter('userName', $userName);

        $userData = $builder->execute()->fetchAssociative();
        if (!$userData) {
            //todo: throw correct exception
            throw new \RuntimeException('User not found');
        }

        return new UserDTO(
            $userData['OXID'],
            $userData['OESMOTPCODE'],
            $userData['OESMOTPATTEMPTS'],
            new DateTime($userData['OESMOTPEXPTIME'])
        );
    }

    public function addOTPtoUser(string $userId, string $otp, DateTime $expiresAt): bool
    {
        $userModel = $this->userFactory->create();
        $userModel->load($userId);
        $userModel->assign([
            'OESMOTPCODE'       => $otp,
            'OESMOTPEXPTIME'    => $expiresAt->format('Y-m-d H:i:s'),
            'OESMOTPATTEMPTS'   => 0,
        ]);
        $userModel->save();

        return true;
    }

    public function updateAttempts(string $userId, int $attempts): void
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
            'OESMOTPCODE'     => '',
            'OESMOTPEXPTIME'  => 0,
            'OESMOTPATTEMPTS' => 0,
        ]);
        $userModel->save();
    }

    public function getUserPasswordHash(string $userName): string
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->select('OXPASSWORD')
            ->from('oxuser')
            ->where('oxusername = :userName')
            ->setParameter('userName', $userName);

        return $builder->execute()->fetchOne();
    }
}
