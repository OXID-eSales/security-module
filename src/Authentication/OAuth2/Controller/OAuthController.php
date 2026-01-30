<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollectorInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserServiceInterface;
use OxidEsales\SecurityModule\Authentication\Session\SessionKeys;

class OAuthController extends FrontendController
{
    public function login(): void
    {
        $providerName = $_GET['provider'] ?? '';

        $providerCollector = $this->getService(ProviderCollectorInterface::class);

        $provider = $providerCollector->getProvider($providerName);

        Registry::getUtils()->redirect($provider->getAuthorizationUrl());
    }

    public function redirect(): void
    {
        $providerName = $_GET['provider'] ?? '';

        $provider = $this
            ->getService(ProviderCollectorInterface::class)
            ->getProvider($providerName);

        $accessToken = $provider->getAccessToken($_GET['code']);

        $userDTO = $provider->getUserInfo($accessToken);

        $this
            ->getService(UserServiceInterface::class)
            ->login($userDTO);

        $redirectUrl = Registry::getSession()->getVariable(SessionKeys::AUTH_REDIRECT_URL);
        if (!$redirectUrl) {
            $redirectUrl = Registry::getConfig()->getShopHomeUrl();
        }

        Registry::getUtils()->redirect($redirectUrl, false);
    }
}
