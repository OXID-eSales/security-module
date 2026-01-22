<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserFactoryInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Repository\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private AuthorizeServiceInterface $authorizeService,
        private UserRepositoryInterface $userRepository,
        private UserFactoryInterface $userFactory,
        private PasswordServiceBridgeInterface $pwdServiceBridge,
        private SessionInterface $session,
        private Utils $utils,
    ) {
    }

    public function handleLogin(string $userName): void
    {
        $this->session->set(AuthorizeService::USER_SESSION_KEY, $userName);

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

    public function finalizeLogin(): void
    {
        $userName = $this->session->get(AuthorizeService::USER_SESSION_KEY);

        $userId = $this->userRepository->getUserIdByUserName($userName);
        $user = $this->userFactory->create();
        $user->load($userId);

        // Reset cached active user so getUser() loads from session
        $user->setUser(null);

        $redirectUrl = $this->getRedirectUrl();

        $shopSession = Registry::getSession();

        // Regenerate session ID like OXID's normal login flow (UserComponent::afterLogin)
        if ($shopSession->isSessionStarted()) {
            $shopSession->regenerateSessionId();
        }

        // Set active view with target URL for OXID's redirect mechanism
        $redirectView = oxNew(\OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Controller\RedirectView::class);
        $redirectView->setRedirectUrl($redirectUrl);
        Registry::getConfig()->setActiveView($redirectView);

        $shopSession->setVariable('usr', $userId);
        $shopSession->setVariable('login-token', $user->getHash($user->getFieldData('oxpassword')));

        $this->userRepository->resetCodeFields($userId);
        $this->clearOTPSessionVariables();
    }

    private function getRedirectUrl(): string
    {
        $storedUrl = $this->session->get(AuthorizeService::OTP_TARGET_URL);

        if ($storedUrl && $this->isInternalUrl($storedUrl)) {
            return $storedUrl;
        }

        return Registry::getConfig()->getShopHomeUrl();
    }

    private function isInternalUrl(string $url): bool
    {
        $shopUrl = Registry::getConfig()->getShopUrl();
        $sslShopUrl = Registry::getConfig()->getSslShopUrl();

        return str_starts_with($url, $shopUrl) || str_starts_with($url, $sslShopUrl);
    }

    private function clearOTPSessionVariables(): void
    {
        $this->session->remove(AuthorizeService::USER_SESSION_KEY);
        $this->session->remove(AuthorizeService::OTP_TARGET_URL);
    }
}
