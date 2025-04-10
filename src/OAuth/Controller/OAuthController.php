<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\OAuth\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\OAuth\Service\ProviderCollectorInterface;

class OAuthController extends FrontendController
{
    public function render()
    {
        $continueWith = Registry::getRequest()->getRequestEscapedParameter('provider');

        $provides = $this->getService(ProviderCollectorInterface::class)->getProviders();

        foreach ($provides as $provider) {
            if ($provider->getName() === $continueWith) {
                $provider->authenticate();
            }
        }
    }
}
