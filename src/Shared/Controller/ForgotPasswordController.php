<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Controller;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use OxidEsales\SecurityModule\EmailVerification\Facade\UserEmailVerificationFacadeInterface;
use OxidEsales\SecurityModule\SessionEviction\Service\ModuleSettingsServiceInterface as SessionEvictionSettings;
use OxidEsales\SecurityModule\Shared\Infrastructure\Factory\UserModelFactoryInterface;

/**
 * @mixin \OxidEsales\Eshop\Application\Controller\ForgotPasswordController
 * @eshopExtension
 */
class ForgotPasswordController extends ForgotPasswordController_parent
{
    private const RESET_SUCCESS_ACTION = 'forgotpwd?success=1';

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
        $userId = $this->getUserIdByUpdateId();
        $result = parent::updatePassword();

        if ($userId !== null && $result === self::RESET_SUCCESS_ACTION) {
            $this->getService(UserEmailVerificationFacadeInterface::class)->confirmEmailAfterPasswordReset($userId);
        }

        $settingsService = $this->getService(SessionEvictionSettings::class);
        if ($settingsService->isEnabled()) {
            $user = $this->getUser();
            if ($user) {
                $user->setSessionLoginToken();
            }
        }

        return $result;
    }

    private function getUserIdByUpdateId(): ?string
    {
        $user = $this->getService(UserModelFactoryInterface::class)->create();

        $user->loadUserByUpdateId((string) $this->getUpdateId());

        return $user->isLoaded() ? (string) $user->getId() : null;
    }
}
