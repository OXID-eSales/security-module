<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Form;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Form\ContactFormCaptchaValidator;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use PHPUnit\Framework\TestCase;

class ContactFormCaptchaValidatorTest extends TestCase
{
    public function testIsValidReturnsTrueOnValidCaptcha(): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn(uniqid());

        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->expects($this->once())
            ->method('validate')
            ->with($requestMock);

        $settingsService = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsService
            ->method('isCaptchaEnabled')
            ->willReturn(true);

        $formValidator = new ContactFormCaptchaValidator($captchaServiceMock, $settingsService, $requestMock);
        $formStub = $this->createStub(FormInterface::class);
        $this->assertTrue($formValidator->isValid($formStub));
        $this->assertEmpty($formValidator->getErrors());
    }

    public function testIsValidReturnsFalseOnInvalidCaptcha(): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn(uniqid());

        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->expects($this->once())
            ->method('validate')
            ->with($requestMock)
            ->willThrowException(new CaptchaValidateException('ERROR_EMPTY_CAPTCHA'));

        $settingsService = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsService
            ->method('isCaptchaEnabled')
            ->willReturn(true);

        $formValidator = new ContactFormCaptchaValidator($captchaServiceMock, $settingsService, $requestMock);

        $this->assertFalse($formValidator->isValid($this->createStub(FormInterface::class)));

        $this->assertEquals(['ERROR_EMPTY_CAPTCHA'], $formValidator->getErrors());
    }

    public function testNotCallValidateWhenCaptchaDisabled(): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->never())
            ->method('getRequestParameter')
            ->with('captcha');

        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->expects($this->never())
            ->method('validate');

        $settingsService = $this->createStub(ModuleSettingsServiceInterface::class);
        $settingsService
            ->method('isCaptchaEnabled')
            ->willReturn(false);

        $formValidator = new ContactFormCaptchaValidator($captchaServiceMock, $settingsService, $requestMock);

        $this->assertTrue($formValidator->isValid($this->createStub(FormInterface::class)));
    }

    public function testGetErrorsReturnsEmptyArrayInitially(): void
    {
        $formValidator = new ContactFormCaptchaValidator(
            $this->createStub(CaptchaServiceInterface::class),
            $this->createStub(ModuleSettingsServiceInterface::class),
            $this->createStub(Request::class)
        );

        $this->assertEmpty($formValidator->getErrors());
    }
}
