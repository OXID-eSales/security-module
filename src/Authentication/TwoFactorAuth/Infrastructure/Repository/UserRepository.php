<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Result;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\UserInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\DTO\User as UserDTO;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Exception\UserNotFoundException;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserFactoryInterface $userFactory,
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {
    }

    public function getUserOTPData(string $userName): UserInterface
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->select([
                'OXID',
                'OESMOTPCODE',
                'OESMOTPATTEMPTS',
                'OESMOTPEXPTIME',
                'OESMOTPLASTSENT',
            ])
            ->from('oxuser')
            ->where('oxusername = :userName')
            ->setParameter('userName', $userName);

        /** @var Result $queryResult */
        $queryResult = $builder->execute();
        $userData = $queryResult->fetchAssociative();
        if (!$userData) {
            throw new UserNotFoundException();
        }

        return new UserDTO(
            $userData['OXID'],
            $userData['OESMOTPATTEMPTS'],
            $userData['OESMOTPCODE'],
            $userData['OESMOTPEXPTIME'] ? new DateTime($userData['OESMOTPEXPTIME']) : null,
            $userData['OESMOTPLASTSENT'] ? new DateTime($userData['OESMOTPLASTSENT']) : null,
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
            'OESMOTPCODE'     => null,
            'OESMOTPEXPTIME'  => null,
            'OESMOTPATTEMPTS' => 0,
            'OESMOTPLASTSENT' => null,
        ]);
        $userModel->save();
    }

    public function getUserPasswordHash(string $userName): ?string
    {
        $builder = $this->queryBuilderFactory->create();
        $builder->select('OXPASSWORD')
            ->from('oxuser')
            ->where('oxusername = :userName')
            ->setParameter('userName', $userName);

        /** @var Result $queryResult */
        $queryResult = $builder->execute();
        $userPass = $queryResult->fetchOne();

        return $userPass ?: null;
    }

    public function markOtpAsSent(string $userId): void
    {
        $userModel = $this->userFactory->create();
        $userModel->load($userId);
        $userModel->assign([
            'OESMOTPLASTSENT' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);
        $userModel->save();
    }
}
