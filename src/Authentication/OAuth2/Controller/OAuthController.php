<?php

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollectorInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserServiceInterface;

class OAuthController extends FrontendController
{
    public function render()
    {
        $providerCollector = $this->getService(ProviderCollectorInterface::class);

        $provider = $providerCollector
            ->getProvider($_GET['provider'])
            ->getClient();

        Registry::getUtils()->redirect($provider->getAuthorizationUrl(), 302);

        exit;
    }

    public function redirect()
    {
        $provider = $this
            ->getService(ProviderCollectorInterface::class)
            ->getProvider('facebook');

        $provider->getClient();

        $accessToken = $provider->getAccessToken($_GET['code']);

        $userDataType = $provider->getUserInfo($accessToken);

        $this
            ->getService(UserServiceInterface::class)
            ->login($userDataType);

        Registry::getUtils()->redirect('', 302);
    }
}
