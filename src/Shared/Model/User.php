<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Model;

use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\InputValidator;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;

/**
 * User model extended
 *
 * @mixin User
 * @eshopExtension
 */
class User extends User_parent
{
    public function checkValues($sLogin, $sPassword, $sPassword2, $aInvAddress, $aDelAddress): void
    {
        /** @var InputValidator $oInputValidator */
        $oInputValidator = Registry::getInputValidator();

        $captcha = Registry::getRequest()->getRequestParameter('captcha');
        try {
            $oInputValidator->validateCaptchaField($captcha);
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
            /** @var InputValidator $oInputValidator */
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
        /** @var InputValidator $oInputValidator */
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
