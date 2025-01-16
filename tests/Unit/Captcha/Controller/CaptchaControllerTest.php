<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Controller;

use OxidEsales\SecurityModule\Captcha\Service\CaptchaAudioServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Transput\ResponseInterface;
use PHPUnit\Framework\TestCase;

class CaptchaControllerTest extends TestCase
{
    public function testReloadMethod(): void
    {
        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->expects($this->once())
            ->method('generate')
            ->willReturn($imageData = uniqid());

        $responseServiceMock = $this->createMock(ResponseInterface::class);
        $responseServiceMock->expects($this->once())
            ->method('responseAsImage')
            ->with($this->equalTo(base64_encode($imageData)));

        $controller = $this->getMockBuilder(CaptchaController::class)
            ->onlyMethods(['getService'])
            ->getMock();

        $controller->expects($this->exactly(2))
            ->method('getService')
            ->willReturnCallback(function ($service) use ($captchaServiceMock, $responseServiceMock) {
                if ($service === CaptchaServiceInterface::class) {
                    return $captchaServiceMock;
                }
                if ($service === ResponseInterface::class) {
                    return $responseServiceMock;
                }
                throw new \InvalidArgumentException("Unknown service: $service");
            });

        $controller->reload();
    }

    public function testPlayMethod(): void
    {
        $captchaAudioServiceMock = $this->createMock(CaptchaAudioServiceInterface::class);
        $captchaAudioServiceMock->expects($this->once())
            ->method('generate')
            ->willReturn($audioData = uniqid());

        $responseServiceMock = $this->createMock(ResponseInterface::class);
        $responseServiceMock->expects($this->once())
            ->method('responseAsAudio')
            ->with($this->equalTo($audioData));

        $controller = $this->getMockBuilder(CaptchaController::class)
            ->onlyMethods(['getService'])
            ->getMock();

        $controller->expects($this->exactly(2))
            ->method('getService')
            ->willReturnCallback(function ($service) use ($captchaAudioServiceMock, $responseServiceMock) {
                if ($service === CaptchaAudioServiceInterface::class) {
                    return $captchaAudioServiceMock;
                }
                if ($service === ResponseInterface::class) {
                    return $responseServiceMock;
                }
                throw new \InvalidArgumentException("Unknown service: $service");
            });

        $controller->play();
    }
}
