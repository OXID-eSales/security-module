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
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\UserServiceInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException as ImageCaptchaException;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Exception\CaptchaValidateException as HoneyPotCaptchaException;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\ModuleSettingsServiceInterface
    as TwoFASettingsServiceInterface;
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
        if ($this->isCaptchaEnabled() && $this->shouldValidateCaptcha()) {
            /** @var InputValidator $oInputValidator */
            $oInputValidator = Registry::getInputValidator();
            $captchaService = $this->getService(CaptchaServiceInterface::class);

            try {
                $captchaService->validate(
                    Registry::getRequest()
                );
            } catch (ImageCaptchaException $e) {
                $oInputValidator->addValidationError(
                    "captcha",
                    oxNew(
                        InputException::class,
                        Registry::getLang()->translateString($e->getMessage())
                    )
                );
            } catch (HoneyPotCaptchaException $e) {
                throw $e;
            }
        }

        parent::checkValues($sLogin, $sPassword, $sPassword2, $aInvAddress, $aDelAddress);
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function login($userName, $password, $setSessionCookie = false): bool
    {
        //todo: login should be reworded, disabled captcha is killing OTP login flow
        if (!$this->isCaptchaEnabled()) {
//            return parent::login($userName, $password, $setSessionCookie);
        }

        if (!$this->isAdmin()) {
            $captchaService = $this->getService(CaptchaServiceInterface::class);

            try {
                $captchaService->validate(
                    Registry::getRequest()
                );
            } catch (ImageCaptchaException | HoneyPotCaptchaException $e) {
                throw oxNew(UserException::class, $e->getMessage());
            }
        }

        if (!$this->isOTPEnabled() || $this->isAdmin()) {
            return parent::login($userName, $password, $setSessionCookie);
        }

        $userService = $this->getService(UserServiceInterface::class);
        if (!$userService->checkPassword($userName, $password)) {
            return false; // invalid login
        }

        //todo: re-login will send new OTP, should we avoid that?
        $userService->handleLogin($userName);

        //todo: redirect to correct page decided by verificator method? (otp: otp page, TOTP: totp page, etc)
        Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=twofactorauth');

        return false;
    }

    private function isCaptchaEnabled(): bool
    {
        $settingsService = $this->getService(CaptchaSettingsServiceInterface::class);
        return $settingsService->isCaptchaEnabled() || $settingsService->isHoneyPotCaptchaEnabled();
    }

    private function isOTPEnabled(): bool
    {
        $settingsService = $this->getService(TwoFASettingsServiceInterface::class);
        return $settingsService->isTwoFactorAuthEnabled();
    }

    protected function shouldValidateCaptcha(): bool
    {
        return !$this->getUser();
    }
}
