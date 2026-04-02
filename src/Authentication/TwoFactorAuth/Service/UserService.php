<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service;

use OxidEsales\Eshop\Core\Utils;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectServiceInterface;
use OxidEsales\SecurityModule\Authentication\Session\SessionKeys;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Infrastructure\Factory\UserModelFactoryInterface;

readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private AuthorizeServiceInterface $authorizeService,
        private UserModelFactoryInterface $userFactory,
        private SessionInterface $session,
        private Utils $utils,
        private InternalRedirectServiceInterface $redirectService,
    ) {
    }

    public function handleLogin(string $userId): void
    {
        $this->session->set(AuthorizeService::USER_SESSION_KEY, $userId);

        $this->authorizeService->generate();

        $redirectUrl = $this->authorizeService->getVerificationUrl();
        $this->utils->redirect($redirectUrl);
    }

    public function finalizeLogin(): void
    {
        $userId = $this->session->get(AuthorizeService::USER_SESSION_KEY);
        $user = $this->userFactory->create();
        $user->load($userId);
        $redirectUrl = $this->redirectService->getRedirectUrl();

        $this->session->set('OTP_PASS', $userId);
        /** @phpstan-ignore argument.type (password is null because user already authenticated via OTP) */
        $user->login($user->getFieldData('oxusername'), null, false);
        $this->clearOTPSessionVariables();
        $this->utils->redirect($redirectUrl, false);
    }

    public function clearOTPSessionVariables(): void
    {
        $this->session->remove(AuthorizeService::USER_SESSION_KEY);
        $this->session->remove(SessionKeys::AUTH_REDIRECT_URL);
        $this->session->remove('OTP_PASS');
    }
}
