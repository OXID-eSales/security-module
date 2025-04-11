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

class NewsletterController extends NewsletterController_parent
{
    public function send(): ?bool
    {
        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if (!$settingsService->isCaptchaEnabled() && !$settingsService->isHoneyPotCaptchaEnabled()) {
            return parent::send();
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

        return parent::send();
    }
}
