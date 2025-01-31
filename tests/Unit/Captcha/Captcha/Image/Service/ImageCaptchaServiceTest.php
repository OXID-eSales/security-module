<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Captcha\Image\Service;

use OxidEsales\Eshop\Core\Request;
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
    public function testGenerateStoresCaptchaInSession(): void
    {
        $builder = $this->createMock(ImageCaptchaBuilderInterface::class);
        $builder->method('getContent')->willReturn(substr(uniqid(), -6));
        $builder->method('build')->willReturn($imgData = uniqid());

        $service = $this->getSut($builder);

        $this->assertEquals($imgData, $service->generate());
    }

    public function testValidateWithValidCaptcha(): void
    {
        $captchaText = substr(uniqid(), -6);
        $requestMock = $this->mockRequest('captcha', $captchaText);

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->willReturnMap([
            ['captcha', $captchaText],
            ['captcha_expiration', time() + 60]
        ]);

        $service = $this->getSut(
            session: $session
        );
        $service->validate($requestMock);
    }

    public function testValidateWithInvalidCaptcha(): void
    {
        $captchaText = substr(uniqid(), -6);
        $requestMock = $this->mockRequest('captcha', substr(uniqid(), -6));

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->willReturnMap([
            ['captcha', $captchaText],
            ['captcha_expiration', time() + 60]
        ]);

        $service = $this->getSut(
            captchaValidator: new ImageCaptchaValidator(),
            session: $session
        );
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');
        $service->validate($requestMock);
    }

    public function testValidateWithEmptyCaptcha(): void
    {
        $captchaText = substr(uniqid(), -6);
        $requestMock = $this->mockRequest('captcha', '');

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->willReturnMap([
            ['captcha', $captchaText],
            ['captcha_expiration', time() + 60]
        ]);

        $service = $this->getSut(
            captchaValidator: new ImageCaptchaValidator(),
            session: $session
        );
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_EMPTY_CAPTCHA');
        $service->validate($requestMock);
    }

    public function testValidExpiredCaptcha(): void
    {
        $captchaText = substr(uniqid(), -6);
        $requestMock = $this->mockRequest('captcha', $captchaText);

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->willReturnMap([
            ['captcha', $captchaText],
            ['captcha_expiration', 1]
        ]);

        $this->expectException(CaptchaValidateException::class);

        $service = $this->getSut(
            captchaValidator: new ImageCaptchaValidator(),
            session: $session
        );
        $service->validate($requestMock);
    }

    public function testValidateWithExpiredCaptcha(): void
    {
        $captchaText = substr(uniqid(), -6);
        $requestMock = $this->mockRequest('captcha', $captchaText);

        $session = $this->createMock(Session::class);
        $session->method('getVariable')->willReturnMap([
            ['captcha', $captchaText],
            ['captcha_expiration', 1]
        ]);

        $service = $this->getSut(
            captchaValidator: new ImageCaptchaValidator(),
            session: $session
        );
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_EXPIRED_CAPTCHA');
        $service->validate($requestMock);
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

    protected function mockRequest(string $paramenter, string $value)
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->once())
            ->method('getRequestParameter')
            ->with($paramenter)
            ->willReturn($value);

        return $requestMock;
    }
}
