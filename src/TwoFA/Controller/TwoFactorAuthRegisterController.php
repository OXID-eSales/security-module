<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\TwoFA\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\TwoFA\Service\TwoFactorAuthInterface;

class TwoFactorAuthRegisterController extends FrontendController
{
    protected $template = '@oe_security_module/templates/2fa/two-factor-auth-registration';

    public function render()
    {
        //Display QR Code after registration
        $template = parent::render();

        $user = $this->getUser();
        if (!$user) {
            Registry::getUtils()->redirect(
                Registry::getConfig()->getShopHomeUrl()
            );
        }

        $this->addTplParam(
            'qrcode',
            $this->getService(TwoFactorAuthInterface::class)->QRCodeGenerate(
                $user->getFieldData('OXUSERNAME')
            )
        );

        return $template;
    }
}
