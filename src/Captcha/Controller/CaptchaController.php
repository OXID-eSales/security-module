<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Controller;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;

class CaptchaController extends WidgetController
{
    public function reload(): void
    {
        $image = $this->getService(CaptchaServiceInterface::class)->generate();

        //todo: use transput
        $oUtils = \OxidEsales\Eshop\Core\Registry::getUtils();
        $oUtils->showMessageAndExit('data:image/jpeg;base64,' . base64_encode($image));
    }
}
