<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Shop;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;

class NewsletterController extends NewsletterController_parent
{
    public function send(): void
    {
        $inputValidator = Registry::getInputValidator();

        try {
            $inputValidator->validateCaptchaField();
        } catch (CaptchaValidateException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            return;
        }

        parent::send();
    }
}
