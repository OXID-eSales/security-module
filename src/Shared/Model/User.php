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
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
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
        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if ($settingsService->isCaptchaEnabled()) {
            /** @var InputValidator $oInputValidator */
            $oInputValidator = Registry::getInputValidator();
            $captchaService = $this->getService(CaptchaServiceInterface::class);

            try {
                $captchaService->validate(
                    Registry::getRequest()
                );
            } catch (CaptchaValidateException $e) {
                $oInputValidator->addValidationError(
                    "captcha",
                    oxNew(
                        InputException::class,
                        Registry::getLang()->translateString($e->getMessage())
                    )
                );
            }
        }

        parent::checkValues($sLogin, $sPassword, $sPassword2, $aInvAddress, $aDelAddress);
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function login($userName, $password, $setSessionCookie = false): bool
    {
        //todo: checks if 2FA is enabled at all or for user
        $login = parent::login($userName, $password, $setSessionCookie);

        if ($this->isAdmin()) {
            return $login;
        }

        Registry::getUtils()->redirect(
            Registry::getConfig()->getShopHomeUrl() . 'cl=2fa&setsessioncookie=' . $setSessionCookie
        );

        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if (!$settingsService->isCaptchaEnabled()) {
            return $login;
        }

        if (!$this->isAdmin()) {
            $captchaService = $this->getService(CaptchaServiceInterface::class);

            try {
                $captchaService->validate(
                    Registry::getRequest()
                );
            } catch (CaptchaValidateException $e) {
                throw oxNew(UserException::class, $e->getMessage());
            }
        }

        return $login;
    }
}
