<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Shop;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;

class UserComponent extends UserComponent_parent
{
    public function login()
    {
        if (!$this->isCaptchaEnabled()) {
            return parent::login();
        }

        try {
            $this->getService(CaptchaServiceInterface::class)
                ->validate(Registry::getRequest());
        } catch (StandardException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            $this->setLoginStatus(USER_LOGIN_FAIL);
            return 'user';
        }

        return parent::login();
    }

    public function createUser()
    {
        if (!$this->isCaptchaEnabled()) {
            return parent::createUser();
        }

        try {
            $this->getService(CaptchaServiceInterface::class)
                ->validate(Registry::getRequest());
        } catch (StandardException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            return false;
        }

        return parent::createUser();
    }

    private function isCaptchaEnabled(): bool
    {
        $settings = $this->getService(ModuleSettingsServiceInterface::class);
        return $settings->isCaptchaEnabled() || $settings->isHoneyPotCaptchaEnabled();
    }
}
