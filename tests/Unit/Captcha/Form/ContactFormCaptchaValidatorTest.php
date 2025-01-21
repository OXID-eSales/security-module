<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Form;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Form\ContactFormCaptchaValidator;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaServiceInterface;
use PHPUnit\Framework\TestCase;

class ContactFormCaptchaValidatorTest extends TestCase
{
    public function testIsValidReturnsTrueOnValidCaptcha(): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->once())
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn($captcha = uniqid());

        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->expects($this->once())
            ->method('validate')
            ->with($captcha);

        $formValidator = new ContactFormCaptchaValidator($captchaServiceMock, $requestMock);
        $this->assertTrue($formValidator->isValid($this->createMock(FormInterface::class)));
        $this->assertEmpty($formValidator->getErrors());
    }

    public function testIsValidReturnsFalseOnInvalidCaptcha(): void
    {
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->once())
            ->method('getRequestParameter')
            ->with('captcha')
            ->willReturn($captcha = uniqid());

        $captchaServiceMock = $this->createMock(CaptchaServiceInterface::class);
        $captchaServiceMock->expects($this->once())
            ->method('validate')
            ->with($captcha)
            ->willThrowException(new CaptchaValidateException('ERROR_EMPTY_CAPTCHA'));

        $formValidator = new ContactFormCaptchaValidator($captchaServiceMock, $requestMock);

        $this->assertFalse($formValidator->isValid($this->createMock(FormInterface::class)));

        $this->assertEquals(['ERROR_EMPTY_CAPTCHA'], $formValidator->getErrors());
    }

    public function testGetErrorsReturnsEmptyArrayInitially(): void
    {
        $formValidator = new ContactFormCaptchaValidator(
            $this->createMock(CaptchaServiceInterface::class),
            $this->createMock(Request::class)
        );

        $this->assertEmpty($formValidator->getErrors());
    }
}
