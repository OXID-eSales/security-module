<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Service;

use PHPUnit\Framework\TestCase;

class CaptchaAudioServiceTest extends TestCase
{
    public function testGenerateAudio()
    {
        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->method('getCaptcha')->willReturn('1234');

        $captchaAudioService = $this->getSut($captchaServiceMock);

        $result = $captchaAudioService->generate();

        $this->assertIsString($result);
        $this->assertStringContainsString('RIFF', $result);
    }

    public function testGenerateAudioNoCaptcha()
    {
        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->method('getCaptcha')->willReturn('');

        $captchaAudioService = $this->getSut($captchaServiceMock);

        $result = $captchaAudioService->generate();

        $this->assertIsString($result);
        $this->assertStringNotContainsString('RIFF', $result);
    }

    public function getSut(
        CaptchaServiceInterface $captchaService = null
    ): CaptchaAudioServiceInterface {
        return new CaptchaAudioService(
            captchaService: $captchaService ?? $this->createStub(CaptchaServiceInterface::class),
        );
    }
}
