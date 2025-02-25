<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Service;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Service\HoneyPotCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Service\CaptchaService;
use OxidEsales\SecurityModule\Captcha\Service\Exception\InvalidCaptchaTypeException;
use PHPUnit\Framework\TestCase;

class CaptchaServiceTest extends TestCase
{
    public function testWrongValidatorType()
    {
        $this->expectException(InvalidCaptchaTypeException::class);

        new CaptchaService([new \stdClass()]);
    }

    public function testValidationWithoutValidators(): void
    {
        $request = $this->createMock(Request::class);

        $passwordValidatorChain = new CaptchaService([]);
        $passwordValidatorChain->validate($request);

        $this->addToAssertionCount(1);
    }

    public function testValidationWithInactiveValidatorDoesNotTriggerValidation(): void
    {
        $request = $this->createMock(Request::class);

        $validatorMock = $this->createMock(ImageCaptchaServiceInterface::class);
        $validatorMock->method('isEnabled')->willReturn(false);
        $validatorMock->expects($this->never())->method('validate');

        $passwordValidatorChain = new CaptchaService([$validatorMock]);
        $passwordValidatorChain->validate($request);

        $this->addToAssertionCount(1);
    }

    public function testValidatorWillThrowFirstFailingValidatorException(): void
    {
        $request = $this->createMock(Request::class);

        $validator1Mock = $this->createMock(ImageCaptchaServiceInterface::class);
        $validator1Mock->method('isEnabled')->willReturn(true);

        $validator2Mock = $this->createMock(HoneyPotCaptchaServiceInterface::class);
        $validator2Mock->method('isEnabled')->willReturn(true);
        $expectedException = new CaptchaValidateException();
        $validator2Mock->method('validate')->willThrowException($expectedException);

        $this->expectException(CaptchaValidateException::class);

        $passwordValidatorChain = new CaptchaService([
            $validator1Mock,
            $validator2Mock
        ]);

        $this->expectExceptionObject($expectedException);
        $passwordValidatorChain->validate($request);
    }

    public function testCaptchaGenerate(): void
    {
        $captcha1Mock = $this->createMock(ImageCaptchaServiceInterface::class);
        $captcha1Mock->method('isEnabled')->willReturn(true);
        $captcha1Mock->method('getName')->willReturn($name = uniqid());
        $captcha1Mock->method('generate')->willReturn($content = uniqid());

        $passwordValidatorChain = new CaptchaService([
            $captcha1Mock
        ]);

        $generators = $passwordValidatorChain->generate();
        $this->assertSame(
            [
                $name => $content
            ],
            $generators
        );
    }

    public function testCaptchaGenerateWithInactiveCaptcha(): void
    {
        $captcha1Mock = $this->createMock(ImageCaptchaServiceInterface::class);
        $captcha1Mock->method('isEnabled')->willReturn(true);
        $captcha1Mock->method('getName')->willReturn($name = uniqid());
        $captcha1Mock->method('generate')->willReturn($content = uniqid());

        $captcha2Mock = $this->createMock(HoneyPotCaptchaServiceInterface::class);
        $captcha2Mock->method('isEnabled')->willReturn(false);

        $passwordValidatorChain = new CaptchaService([
            $captcha1Mock,
            $captcha2Mock
        ]);

        $generators = $passwordValidatorChain->generate();
        $this->assertSame(
            [
                $name => $content
            ],
            $generators
        );
    }
}
