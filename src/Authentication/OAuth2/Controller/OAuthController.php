<?php

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
        $provider = $this
            ->getService(ProviderCollectorInterface::class)
            ->getProvider('facebook');

        $accessToken = $provider->getAccessToken($_GET['code']);

        $userDataObject = $provider->getUserInfo($accessToken);

        $this
            ->getService(UserServiceInterface::class)
            ->login($userDataObject);

        Registry::getUtils()->redirect('');
    }
}
