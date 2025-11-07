<?php

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use League\OAuth2\Client\Token\AccessTokenInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollectorInterface;

class OAuthController extends FrontendController
{
    public function redirect()
    {
        $providerName = Registry::getRequest()->getRequestEscapedParameter('provider');
        $collector = $this->getService(ProviderCollectorInterface::class);
        $provider = $collector->getProvider($providerName);

        if (!$provider) {
            Registry::getUtilsView()->addErrorToDisplay("Unknown provider: $providerName");
            return;
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $authUrl = $provider->getAuthorizationUrl($state);
        Registry::getUtils()->redirect($authUrl);
    }

    public function callback()
    {
        $providerName = Registry::getRequest()->getRequestEscapedParameter('provider');
        $collector = $this->getService(ProviderCollectorInterface::class);
        $provider = $collector->getProvider($providerName);

        if (!$provider) {
            Registry::getUtilsView()->addErrorToDisplay("Unknown provider: $providerName");
            return;
        }

        $state = Registry::getRequest()->getRequestEscapedParameter('state');
        if ($state !== ($_SESSION['oauth_state'] ?? null)) {
            Registry::getUtilsView()->addErrorToDisplay('Invalid OAuth state');
            return;
        }

        try {
            $code = Registry::getRequest()->getRequestEscapedParameter('code');
            $token = $provider->getAccessToken($code);

            if (!$provider->validateToken($token)) {
                throw new \RuntimeException('Token invalid or expired');
            }

            $userInfo = $provider->getUserInfo($token);

            // Now handle login or registration (customize this part)
            $this->handleUserLogin($providerName, $userInfo);

            Registry::getUtils()->redirect('index.php?cl=account');

        } catch (\Throwable $e) {
            Registry::getLogger()->error('OAuth callback error: ' . $e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay('OAuth login failed. Please try again.');
            Registry::getUtils()->redirect('index.php?cl=login');
        }
    }

    protected function handleUserLogin(string $providerName, array $userInfo): void
    {
        // TODO: check if user exists by provider ID or email, then log in or create
        // Example: $userService->loginOrRegisterViaOAuth($providerName, $userInfo);
    }
}
