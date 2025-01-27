<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Image\Service;

use OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder\ImageCaptchaBuilderInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaService;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator\ImageCaptchaValidator;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator\ImageCaptchaValidatorInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;

class ImageCaptchaServiceTest extends TestCase
{
    public function testGenerateStoresCaptchaInSession()
    {
        $_SESSION = [];
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));
        $builder->method('build')->willReturn($imgData = uniqid());

        $service = $this->getSut($builder);
        $image = $service->generate();

        $this->assertEquals($captchaText, $_SESSION['captcha']);
        $this->assertGreaterThan(time(), $_SESSION['captcha_expiration']);
        $this->assertEquals($imgData, $image);
    }

    public function testValidateWithValidCaptcha()
    {
        $_SESSION['captcha_expiration'] = time() + 60;//todo: rework after captcha validation refactoring
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));

        $service = $this->getSut($builder);
        $service->generate();
        $service->validate($captchaText, $captchaText);
    }

    public function testValidateWithInvalidCaptcha()
    {
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));

        $service = $this->getSut($builder, new ImageCaptchaValidator());
        $service->generate();
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');
        $service->validate(substr(uniqid(), -6), $captchaText);
    }

    public function testValidateWithEmptyCaptcha()
    {
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));

        $service = $this->getSut($builder, new ImageCaptchaValidator());
        $service->generate();
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_EMPTY_CAPTCHA');
        $service->validate('', $captchaText);
    }

    public function testValidExpiredCaptcha(): void
    {
        //todo: rework after captcha validation refactoring
        $captchaText = substr(uniqid(), -6);
        $_SESSION['captcha_expiration'] = 1;
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText);

        $this->expectException(CaptchaValidateException::class);

        $service = $this->getSut($builder, new ImageCaptchaValidator());
        $service->validate($captchaText, $captchaText);
    }

    public function testValidateWithExpiredCaptcha()
    {
        $captchaText = substr(uniqid(), -6);

        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText);

        $service = $this->getSut($builder, new ImageCaptchaValidator());
        $service->generate();
        $service->validate($captchaText, $captchaText);
    }

    public function getSut(
        ImageCaptchaBuilderInterface $captchaBuilder = null,
        ImageCaptchaValidatorInterface $captchaValidator = null,
        ModuleSettingsServiceInterface $settingsService = null
    ): ImageCaptchaServiceInterface {
        return new ImageCaptchaService(
            captchaBuilder: $captchaBuilder ?? $this->createStub(ImageCaptchaServiceInterface::class),
            captchaValidator: $captchaValidator ?? $this->createStub(ImageCaptchaValidatorInterface::class),
            settingsService: $settingsService ?? $this->createConfiguredStub(
                ModuleSettingsServiceInterface::class,
                ['getCaptchaLifeTime' => '15M']
            )
        );
    }
}
