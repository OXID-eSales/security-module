<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\AuthenticationServiceInterface;
use OxidEsales\SecurityModule\Authentication\OAuth2\Transput\OAuthRequestInterface;
use OxidEsales\SecurityModule\Authentication\Service\InternalRedirectServiceInterface;

class OAuthController extends FrontendController
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly OAuthRequestInterface $oauthRequest,
        private readonly InternalRedirectServiceInterface $redirectService,
        private readonly Utils $utils,
    ) {
        parent::__construct();
    }

    public function login(): void
    {
        $authorizationUrl = $this->authenticationService->getAuthorizationUrl(
            $this->oauthRequest->getProvider()
        );

        $this->utils->redirect($authorizationUrl);
    }

    public function redirect(): void
    {
        if ($this->oauthRequest->hasError()) {
            $this->utils->redirect($this->redirectService->getRedirectUrl(), false);
            return;
        }

        $this->authenticationService->handleCallback(
            $this->oauthRequest->getProvider(),
            $this->oauthRequest->getCode()
        );

        $this->utils->redirect($this->redirectService->getRedirectUrl(), false);
    }
}
