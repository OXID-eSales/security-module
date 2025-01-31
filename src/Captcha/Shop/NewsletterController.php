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
        $request = Registry::getRequest();

        $settingsService = $this->getService(ModuleSettingsServiceInterface::class);
        if (!$settingsService->isCaptchaEnabled()) {
            return parent::send();
        }

        $captchaService = $this->getService(CaptchaServiceInterface::class);
        $captcha = (string) $request->getRequestParameter('captcha');

        try {
            $captchaService->honeyPotValidate($request);

            $captchaService->validate($captcha);
        } catch (CaptchaValidateException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            return false;
        }

        return parent::send();
    }
}
