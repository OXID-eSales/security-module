<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Shop;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Shared\Core\InputValidator;

class NewsletterController extends NewsletterController_parent
{
    public function send(): bool
    {
        /** @var InputValidator $inputValidator */
        $inputValidator = Registry::getInputValidator();

        try {
            $inputValidator->validateCaptchaField();
        } catch (CaptchaValidateException $e) {
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
            return false;
        }

        return parent::send();
    }
}
