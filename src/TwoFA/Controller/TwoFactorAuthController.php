<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\TwoFA\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\TwoFA\Service\TwoFactorAuthInterface;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends FrontendController
{
    protected $_sThisTemplate = '@oe_security_module/templates/2fa/two-factor-auth';

    public function render()
    {
        $template = parent::render();

        $code = Registry::getRequest()->getRequestEscapedParameter('code');
        if ($code) {
            $secret = $this->getService(TwoFactorAuthInterface::class)->secretGenerate();

            $GA = new Google2FA();
            $valid = $GA->verify(
                $code,
                $secret,
            );

            if ($valid) {
                //todo: set correct cookies so user is logged in after verification

                Registry::getUtils()->redirect(
                    Registry::getConfig()->getShopHomeUrl() . '?cl=account'
                );
            }
        }

        return $template;
    }
}
