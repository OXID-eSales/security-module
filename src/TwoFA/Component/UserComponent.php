<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\TwoFA\Component;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\SecurityModule\TwoFA\Service\TwoFactorAuthInterface;

class UserComponent extends UserComponent_parent
{
    public function registerUser()
    {
        $registration = parent::registerUser();

        //todo: module setting to use OTP (sms, email) or TOTP (time based password)
        if ($registration) {
            $this->handleOTP();

            $this->handleTOTP();
        }
    }

    private function handleOTP()
    {
        $user = $this->getUser();

        //This is OTP password, saved to MySQL and send as email/sms
        $twoFactorAuth = ContainerFactory::getInstance()->getContainer()->get(TwoFactorAuthInterface::class);
        $OTPCode = $twoFactorAuth->generateOTPCode();

        //Todo: create own field for OTP password
        $user->assign([
            'OXADDINFO' => $OTPCode,
        ]);
        $user->save();

        $mail = oxNew(\OxidEsales\Eshop\Core\Email::class);
        $mail->setUser($user);
        $mail->setRecipient($user->oxuser__oxusername->value);
        $mail->setBody('your code is ' . $OTPCode);
        $mail->send();

        Registry::getUtils()->redirect(
            Registry::getConfig()->getShopHomeUrl() . 'cl=2fa'
        );
    }

    private function handleTOTP()
    {
        //In case we have TOTP redirect to QR Code page
        Registry::getUtils()->redirect(
            Registry::getConfig()->getShopHomeUrl() . 'cl=2faregister&success=1'
        );
    }
}
