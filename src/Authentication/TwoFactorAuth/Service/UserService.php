<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private AuthorizeServiceInterface $authorizeService,
        private UserRepositoryInterface $userRepository,
        private PasswordServiceBridgeInterface $passwordServiceBridge,
        private SessionInterface $session
    ) {
    }

    public function handleLogin($userName): void
    {
        $this->session->set(AuthorizeService::USER_SESSION_KEY, $userName);
        $this->session->set(
            AuthorizeService::OTP_TARGET_URL,
            //todo: bind registry
            Registry::getRequest()->getRequestUrl()
        );

        $this->authorizeService->generate();

        //todo: return full url
        $redirectUrl = $this->authorizeService->getVerificationUrl();
        Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=' . $redirectUrl);
    }

    public function checkPassword(string $userName, string $password): bool
    {
        //todo: got exception if user not found
        $userPasswordHash = $this->userRepository->getUserPasswordHash($userName);

        return $this->passwordServiceBridge
            ->verifyPassword($password, $userPasswordHash);
    }
}
