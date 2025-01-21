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
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Shared\Core\InputValidator;

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
        /** @var InputValidator $oInputValidator */
        $oInputValidator = Registry::getInputValidator();
        $captchaValidator = $this->getService(CaptchaServiceInterface::class);
        $captcha = Registry::getRequest()->getRequestParameter('captcha');

        try {
            $captchaValidator->validate($captcha);
        } catch (CaptchaValidateException $e) {
            $oInputValidator->addValidationError(
                "captcha",
                oxNew(
                    InputException::class,
                    Registry::getLang()->translateString($e->getMessage())
                )
            );
        }

        parent::checkValues($sLogin, $sPassword, $sPassword2, $aInvAddress, $aDelAddress);
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function login($userName, $password, $setSessionCookie = false): bool
    {
        if (!$this->isAdmin()) {
            $captchaValidator = $this->getService(CaptchaServiceInterface::class);
            $captcha = Registry::getRequest()->getRequestParameter('captcha');

            try {
                $captchaValidator->validate($captcha);
            } catch (CaptchaValidateException $e) {
                throw oxNew(UserException::class, $e->getMessage());
            }
        }

        return parent::login($userName, $password, $setSessionCookie);
    }
}
