<?php

namespace OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Service;

use OxidEsales\Eshop\Core\Request;

interface HoneyPotCaptchaServiceInterface
{
    public function validate(Request $request): void;
}
