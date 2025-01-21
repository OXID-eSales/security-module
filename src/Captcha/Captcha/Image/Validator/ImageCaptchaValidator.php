<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator;

use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;

class ImageCaptchaValidator implements ImageCaptchaValidatorInterface
{
    /**
     * @param string $userCaptcha
     * @param string $sessionCaptcha
     * @return void
     * @throws CaptchaValidateException
     */
    public function validate(string $userCaptcha, string $sessionCaptcha): void
    {
        if (!$userCaptcha) {
            throw new CaptchaValidateException('ERROR_EMPTY_CAPTCHA');
        }

//        $sessionCaptcha = (string) $_SESSION['captcha'];

        if (!hash_equals($sessionCaptcha, $userCaptcha)) {
            throw new CaptchaValidateException('ERROR_INVALID_CAPTCHA');
        }
    }
}
