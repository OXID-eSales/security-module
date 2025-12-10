<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private AuthorizeServiceInterface $authorizeService,
        private UserFactoryInterface $userFactory,
        private UserRepositoryInterface $userRepository,
        private PasswordServiceBridgeInterface $passwordServiceBridge,
        private SessionInterface $session
    ) {
    }

    public function handleLogin($userName): void
    {
        $this->authorizeService->generate($userName);

        //todo: this should be handled by authorize service?
        $this->session->set('pending_otp_user', $userName);
        // redirect to controller and rende template
        // use template renderer to render the template
    }

    public function checkPassword(string $userName, string $password): bool
    {
//        var_dump($userId);
//        $userModel = $this->userFactory->create();
//        $userModel->load($userId);

        $userPasswordHash = $this->userRepository->getUserPasswordHash($userName);
//        var_dump($userPasswordHash);
        return $this->passwordServiceBridge
            ->verifyPassword($password, $userPasswordHash);
    }
}
