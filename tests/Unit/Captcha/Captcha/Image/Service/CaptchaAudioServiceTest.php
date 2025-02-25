<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Captcha\Image\Service;

use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\CaptchaAudioService;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\CaptchaAudioServiceInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use PHPUnit\Framework\TestCase;

class CaptchaAudioServiceTest extends TestCase
{
    public function testGenerateAudio()
    {
        $captchaServiceMock = $this->createMock(ImageCaptchaServiceInterface::class);
        $captchaServiceMock->method('getCaptcha')->willReturn('1234');

        $captchaAudioService = $this->getSut($captchaServiceMock);

        $result = $captchaAudioService->generate();

        $this->assertIsString($result);
        $this->assertStringContainsString('RIFF', $result);
    }

    public function testGenerateAudioNoCaptcha()
    {
        $captchaServiceMock = $this->createMock(ImageCaptchaServiceInterface::class);
        $captchaServiceMock->method('getCaptcha')->willReturn('');

        $captchaAudioService = $this->getSut($captchaServiceMock);

        $result = $captchaAudioService->generate();

        $this->assertIsString($result);
        $this->assertStringNotContainsString('RIFF', $result);
    }

    public function getSut(
        ImageCaptchaServiceInterface $imageCaptchaService = null
    ): CaptchaAudioServiceInterface {
        return new CaptchaAudioService(
            imageCaptchaService: $imageCaptchaService ?? $this->createStub(ImageCaptchaServiceInterface::class),
        );
    }
}
