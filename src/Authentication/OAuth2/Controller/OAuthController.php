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

class OAuthController extends FrontendController
{
    public function login(): void
    {
        $providerCollector = $this->getService(ProviderCollectorInterface::class);

        $provider = $providerCollector->getProvider($_GET['provider']);

        Registry::getUtils()->redirect($provider->getAuthorizationUrl());
    }

    public function redirect(): void
    {
        //todo: get provider dynamically
        $provider = $this
            ->getService(ProviderCollectorInterface::class)
            ->getProvider('google');

        $accessToken = $provider->getAccessToken($_GET['code']);

        $userDTO = $provider->getUserInfo($accessToken);

        $this
            ->getService(UserServiceInterface::class)
            ->login($userDTO);

        Registry::getUtils()->redirect('');
    }
}
