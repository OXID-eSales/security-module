<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Shop;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;

class NewsletterController extends NewsletterController_parent
{
    public function send(): ?bool
    {
        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if (!$settingsService->isCaptchaEnabled()) {
            var_dump(23423);
            return parent::send();
        }

        $captchaValidator = $this->getService(CaptchaServiceInterface::class);
        $captcha = (string) Registry::getRequest()->getRequestParameter('captcha');

        try {
            $captchaValidator->validate($captcha);
        } catch (CaptchaValidateException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            return false;
        }

        return parent::send();
    }
}
