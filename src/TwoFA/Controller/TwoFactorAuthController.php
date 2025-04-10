<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\TwoFA\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\TwoFA\Service\TwoFactorAuthInterface;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends FrontendController
{
    protected $template = '@oe_security_module/templates/2fa/two-factor-auth';

    public function render(): string
    {
        $template = parent::render();

        $code = Registry::getRequest()->getRequestEscapedParameter('code');
        if ($code) {
            $this->handleOTP($code);

            $this->handleTOTP($code);
        }

        return $template;
    }

    private function handleOTP(string $code): void
    {
        //In case OTP is used
        $sessionUser =  Registry::getSession()->getVariable('usr');
        $user = oxNew(User::class);
        $user->load($sessionUser);

        //Todo: create own field for OTP password
        if (
            $user->getFieldData('oxaddinfo') == $code
        ) {
            $this->redirectToAccount();
        }
    }


    private function handleTOTP(string $code): void
    {
        //In case TOTP is used
        $secret = $this->getService(TwoFactorAuthInterface::class)->secretGenerate();

        $GA = new Google2FA();
        $valid = $GA->verify(
            $code,
            $secret,
        );

        if ($valid) {
            $this->redirectToAccount();
        }
    }

    private function redirectToAccount(): void
    {
        //todo: set correct cookies so user is logged in after verification

        Registry::getUtils()->redirect(
            Registry::getConfig()->getShopHomeUrl() . '?cl=account'
        );
    }
}
