<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Controller;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\UserServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\ForgotPasswordController
 * @eshopExtension
 */
class ForgotPasswordController extends ForgotPasswordController_parent
{
    public function forgotPassword(): ?bool
    {
        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if (!$settingsService->isCaptchaEnabled() && !$settingsService->isHoneyPotCaptchaEnabled()) {
            return parent::forgotPassword();
        }

        $captchaService = $this->getService(CaptchaServiceInterface::class);

        try {
            $captchaService->validate(
                Registry::getRequest()
            );
        } catch (StandardException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            return false;
        }

        return parent::forgotPassword();
    }

    public function updatePassword()
    {
        $result = parent::updatePassword();

        if ($result === 'forgotpwd?success=1') {
            $this->getService(UserServiceInterface::class)
                ->removeExternalAuthFlag();
        }

        return $result;
    }
}
