<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Service;

use OxidEsales\Eshop\Core\Session;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaService;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use PHPUnit\Framework\TestCase;

class CaptchaServiceTest extends TestCase
{
    public function testCaptchaGetter(): void
    {
        $captcha = uniqid();

        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);
        $session = $this->createStub(Session::class);
        $session->method('getVariable')->willReturn($captcha);

        $captchaService = $this->getSut(
            $imageCaptchaService,
            $session
        );

        $this->assertSame($captcha, $captchaService->getCaptcha());
    }


    public function testCaptchaGenerate(): void
    {
        $image = uniqid();
        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);

        $captchaService = $this->getSut($imageCaptchaService);
        $imageCaptchaService->method('generate')->willReturn($image);

        $this->assertSame($image, $captchaService->generate());
    }

    public function getSut(
        ImageCaptchaServiceInterface $captchaService = null,
        Session $session = null
    ): CaptchaServiceInterface {
        return new CaptchaService(
            captchaService: $captchaService ?? $this->createStub(ImageCaptchaServiceInterface::class),
            session: $session ?? $this->createStub(Session::class)
        );
    }
}
