<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Shared\Core;

use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ProviderCollectorInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAResendableInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Service\TwoFAUserSettingsServiceInterface;
use OxidEsales\SecurityModule\Authentication\TwoFactorAuth\Settings\TwoFAShopSettingsInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaService;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\PasswordPolicy\Service\ModuleSettingsServiceInterface as PasswordSettingsServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface as CaptchaSettingsServiceInterface;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{
    public function getSecurityModulePasswordSettings(): PasswordSettingsServiceInterface
    {
        return $this->getService(PasswordSettingsServiceInterface::class);
    }

    public function getSecurityModuleCaptchaSettings(): CaptchaSettingsServiceInterface
    {
        return $this->getService(CaptchaSettingsServiceInterface::class);
    }

    public function getPasswordLength(): int
    {
        $passwordLength = $this->getSecurityModulePasswordSettings()->getPasswordMinimumLength();
        $shopPasswordLength = Registry::getInputValidator()->getPasswordLength();

        if ($passwordLength < $shopPasswordLength) {
            return $shopPasswordLength;
        }

        return $passwordLength;
    }

    public function getImage(): string
    {
        $images = $this->getService(CaptchaServiceInterface::class)->generate();

        return 'data:image/jpeg;base64,' . base64_encode($images[ImageCaptchaService::CAPTCHA_NAME]);
    }

    // todo-critical: move to controller
    public function getRemainingAttempts(): int
    {
        $twoFAService = $this->getService(TwoFAServiceInterface::class);
        if (!$twoFAService instanceof TwoFAResendableInterface) {
            return 0;
        }

        $userId = $this->getService(TwoFAUserServiceInterface::class)->getPendingUserId();
        return $twoFAService->getRemainingAttempts($userId);
    }

    // todo-critical: move to controller
    public function getResendCooldownRemaining(): int
    {
        $twoFAService = $this->getService(TwoFAServiceInterface::class);
        if (!$twoFAService instanceof TwoFAResendableInterface) {
            return 0;
        }

        $userId = $this->getService(TwoFAUserServiceInterface::class)->getPendingUserId();
        return $twoFAService->getCooldownRemaining($userId);
    }

    // todo-high: questionable if we want this method here at all, its just for one template - controller instead?
    public function isTwoFAEnabledForShop(): bool
    {
        return $this->getService(TwoFAShopSettingsInterface::class)->isTwoFactorAuthEnabled();
    }

    public function isExternalAuthUser(): bool
    {
        $user = $this->getUser();

        return $user && (bool) $user->getFieldData('oesmexternalauth');
    }
}
