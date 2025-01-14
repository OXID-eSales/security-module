<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Controller;

use OxidEsales\Eshop\Application\Component\Widget\WidgetController;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaAudioServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Transput\ResponseInterface;

class CaptchaController extends WidgetController
{
    public function reload(): void
    {
        $image = $this->getService(CaptchaServiceInterface::class)->generate();

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsImage(base64_encode($image));
    }

    public function play(): void
    {
        $audioFile = $this->getService(CaptchaAudioServiceInterface::class)->generate();

        $responseService = $this->getService(ResponseInterface::class);
        $responseService->responseAsAudio($audioFile);
    }
}
