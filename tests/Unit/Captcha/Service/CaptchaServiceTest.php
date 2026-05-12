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
        $request = $this->createStub(Request::class);

        $passwordValidatorChain = new CaptchaService([]);
        $passwordValidatorChain->validate($request);

        $this->addToAssertionCount(1);
    }

    public function testValidationWithInactiveValidatorDoesNotTriggerValidation(): void
    {
        $request = $this->createStub(Request::class);

        $validatorMock = $this->createMock(ImageCaptchaServiceInterface::class);
        $validatorMock->method('isEnabled')->willReturn(false);
        $validatorMock->expects($this->never())->method('validate');

        $passwordValidatorChain = new CaptchaService([$validatorMock]);
        $passwordValidatorChain->validate($request);

        $this->addToAssertionCount(1);
    }

    public function testValidatorWillThrowFirstFailingValidatorException(): void
    {
        $request = $this->createStub(Request::class);

        $validator1Stub = $this->createStub(ImageCaptchaServiceInterface::class);
        $validator1Stub->method('isEnabled')->willReturn(true);

        $validator2Stub = $this->createStub(HoneyPotCaptchaServiceInterface::class);
        $validator2Stub->method('isEnabled')->willReturn(true);
        $expectedException = new CaptchaValidateException();
        $validator2Stub->method('validate')->willThrowException($expectedException);

        $this->expectException(CaptchaValidateException::class);

        $passwordValidatorChain = new CaptchaService([
            $validator1Stub,
            $validator2Stub
        ]);

        $this->expectExceptionObject($expectedException);
        $passwordValidatorChain->validate($request);
    }

    public function testCaptchaGenerate(): void
    {
        $captcha1Stub = $this->createStub(ImageCaptchaServiceInterface::class);
        $captcha1Stub->method('isEnabled')->willReturn(true);
        $captcha1Stub->method('getName')->willReturn($name = uniqid());
        $captcha1Stub->method('generate')->willReturn($content = uniqid());

        $passwordValidatorChain = new CaptchaService([
            $captcha1Stub
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
        $captcha1Stub = $this->createStub(ImageCaptchaServiceInterface::class);
        $captcha1Stub->method('isEnabled')->willReturn(true);
        $captcha1Stub->method('getName')->willReturn($name = uniqid());
        $captcha1Stub->method('generate')->willReturn($content = uniqid());

        $captcha2Stub = $this->createStub(HoneyPotCaptchaServiceInterface::class);
        $captcha2Stub->method('isEnabled')->willReturn(false);

        $passwordValidatorChain = new CaptchaService([
            $captcha1Stub,
            $captcha2Stub
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
