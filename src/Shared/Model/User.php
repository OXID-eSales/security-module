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
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException as ImageCaptchaException;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Exception\CaptchaValidateException as HoneyPotCaptchaException;
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
        if ($settingsService->isCaptchaEnabled() || $settingsService->isHoneyPotCaptchaEnabled()) {
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
        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if (!$settingsService->isCaptchaEnabled() && !$settingsService->isHoneyPotCaptchaEnabled()) {
            return parent::login($userName, $password, $setSessionCookie);
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

        return parent::login($userName, $password, $setSessionCookie);
    }
}
