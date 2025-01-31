<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Service;

use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Service\HoneyPotCaptchaService;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Service\HoneyPotCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaService;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use PHPUnit\Framework\TestCase;

class CaptchaServiceTest extends TestCase
{
    public function testCaptchaGetter(): void
    {
        $captchaText = uniqid();

        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);
        $imageCaptchaService->method('getCaptcha')->willReturn($captchaText);

        $captchaService = $this->getSut($imageCaptchaService);

        $this->assertSame($captchaText, $captchaService->getCaptcha());
    }

    public function testCaptchaExpirationGetter(): void
    {
        $captchaExpiration = time();

        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);
        $imageCaptchaService->method('getCaptchaExpiration')->willReturn($captchaExpiration);

        $captchaService = $this->getSut($imageCaptchaService);

        $this->assertSame($captchaExpiration, $captchaService->getCaptchaExpiration());
    }

    public function testCaptchaGenerate(): void
    {
        $captchaImage = uniqid();
        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);

        $captchaService = $this->getSut($imageCaptchaService);
        $imageCaptchaService->method('generate')->willReturn($captchaImage);

        $this->assertSame($captchaImage, $captchaService->generate());
    }

    public function getSut(
        ImageCaptchaServiceInterface $captchaService = null,
        HoneyPotCaptchaServiceInterface $honeyPotService = null,
    ): CaptchaServiceInterface {
        return new CaptchaService(
            captchaService: $captchaService ?? $this->createStub(ImageCaptchaServiceInterface::class),
            honeyPotService: $honeyPotService ?? $this->createStub(HoneyPotCaptchaServiceInterface::class),
        );
    }
}
