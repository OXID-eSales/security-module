<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private AuthorizeServiceInterface $authorizeService,
        private UserRepositoryInterface $userRepository,
        private PasswordServiceBridgeInterface $pwdServiceBridge,
        private SessionInterface $session,
        private Request $request,
        private Utils $utils,
    ) {
    }

    public function handleLogin(string $userName): void
    {
        $this->session->set(AuthorizeService::USER_SESSION_KEY, $userName);
        $this->session->set(
            AuthorizeService::OTP_TARGET_URL,
            $this->request->getRequestUrl()
        );

        //todo: prevent spam by rate limiting
        $this->authorizeService->generate();

        $redirectUrl = $this->authorizeService->getVerificationUrl();
        $this->utils->redirect($redirectUrl);
    }

    public function checkPassword(string $userName, string $password): bool
    {
        try {
            $userPasswordHash = $this->userRepository->getUserPasswordHash($userName);
        } catch (\Throwable $e) {
            return false;
        }

        if ($userPasswordHash === null) {
            return false;
        }

        return $this->pwdServiceBridge
            ->verifyPassword($password, $userPasswordHash);
    }
}
