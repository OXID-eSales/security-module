<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Image\Service;

use OxidEsales\Eshop\Core\Session;
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
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));
        $builder->method('build')->willReturn($imgData = uniqid());

        $service = $this->getSut($builder);

        $this->assertEquals($imgData, $service->generate());
    }

    public function testValidateWithValidCaptcha()
    {
        $session = $this->createMock(Session::class);
        $session->method('getVariable')->with('captcha_expiration')->willReturn(time() + 60);

        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));

        $service = $this->getSut(
            $builder,
            session: $session
        );
        $service->generate();
        $service->validate($captchaText, $captchaText);
    }

    public function testValidateWithInvalidCaptcha()
    {
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->with('captcha_expiration')->willReturn(time() + 60);

        $service = $this->getSut($builder, new ImageCaptchaValidator(), session: $session);
        $service->generate();
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');
        $service->validate(substr(uniqid(), -6), $captchaText);
    }

    public function testValidateWithEmptyCaptcha()
    {
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText = substr(uniqid(), -6));

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->with('captcha_expiration')->willReturn(time() + 60);

        $service = $this->getSut($builder, new ImageCaptchaValidator(), session: $session);
        $service->generate();
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_EMPTY_CAPTCHA');
        $service->validate('', $captchaText);
    }

    public function testValidExpiredCaptcha(): void
    {
        $captchaText = substr(uniqid(), -6);

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->with('captcha_expiration')->willReturn(1);

        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText);

        $this->expectException(CaptchaValidateException::class);

        $service = $this->getSut(
            $builder,
            new ImageCaptchaValidator(),
            session: $session
        );
        $service->validate($captchaText, $captchaText);
    }

    public function testValidateWithExpiredCaptcha()
    {
        $captchaText = substr(uniqid(), -6);

        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn($captchaText);

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->with('captcha_expiration')->willReturn(1);

        $service = $this->getSut($builder, new ImageCaptchaValidator(), session: $session);
        $service->generate();
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_EXPIRED_CAPTCHA');
        $service->validate($captchaText, $captchaText);
    }

    public function testGetCaptcha(): void
    {
        $captchaText = uniqid();

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getVariable')->with('captcha')->willReturn($captchaText);

        $service = $this->getSut(session: $sessionMock);

        $this->assertSame($captchaText, $service->getCaptcha());
    }

    public function testGetCaptchaExpirationTime(): void
    {
        $captchaExpirationTime = time();

        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getVariable')->with('captcha_expiration')->willReturn($captchaExpirationTime);

        $service = $this->getSut(session: $sessionMock);

        $this->assertSame($captchaExpirationTime, $service->getCaptchaExpiration());
    }

    public function getSut(
        ImageCaptchaBuilderInterface $captchaBuilder = null,
        ImageCaptchaValidatorInterface $captchaValidator = null,
        ModuleSettingsServiceInterface $settingsService = null,
        Session $session = null
    ): ImageCaptchaServiceInterface {
        return new ImageCaptchaService(
            captchaBuilder: $captchaBuilder ?? $this->createStub(ImageCaptchaBuilderInterface::class),
            captchaValidator: $captchaValidator ?? $this->createStub(ImageCaptchaValidatorInterface::class),
            settingsService: $settingsService ?? $this->createConfiguredStub(
                ModuleSettingsServiceInterface::class,
                ['getCaptchaLifeTime' => '15M']
            ),
            session: $session ?? $this->createStub(Session::class),
        );
    }
}
