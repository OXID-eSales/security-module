<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Model;

use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;

/**
 * User model extended
 *
 * @mixin \OxidEsales\Eshop\Application\Model\User
 * @eshopExtension
 */
class User extends User_parent
{
    public function checkValues($sLogin, $sPassword, $sPassword2, $aInvAddress, $aDelAddress): void
    {
        $oInputValidator = Registry::getInputValidator();

        try {
            $oInputValidator->validateCaptchaField();
        } catch (CaptchaValidateException $e) {
            $oInputValidator->addValidationError(
                "captcha",
                oxNew(
                    InputException::class,
                    sprintf(Registry::getLang()->translateString($e->getMessage()))
                )
            );
        }

        parent::checkValues($sLogin, $sPassword, $sPassword2, $aInvAddress, $aDelAddress);
    }

    public function login($userName, $password, $setSessionCookie = false): bool
    {
        if (!$this->isAdmin()) {
            $oInputValidator = Registry::getInputValidator();

            try {
                $oInputValidator->validateCaptchaField();
            } catch (CaptchaValidateException $e) {
                throw oxNew(UserException::class, $e->getMessage());
            }
        }

        return parent::login($userName, $password, $setSessionCookie);
    }

    public function setNewsSubscription($blSubscribe, $blSendOptIn, $blForceCheckOptIn = false): bool
    {
        $oInputValidator = Registry::getInputValidator();

        try {
            $oInputValidator->validateCaptchaField();
        } catch (CaptchaValidateException $e) {
//            throw oxNew(UserException::class, $e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            return false;
        }

        return parent::setNewsSubscription($blSubscribe, $blSendOptIn, $blForceCheckOptIn);
    }
}
