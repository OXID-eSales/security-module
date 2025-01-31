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

//    public function testCaptchaGetter(): void
//    {
//        $captchaText = uniqid();
//
//        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);
//        $imageCaptchaService->method('getCaptcha')->willReturn($captchaText);
//
//        $captchaService = $this->getSut($imageCaptchaService);
//
//        $this->assertSame($captchaText, $captchaService->getCaptcha());
//    }

//    public function testCaptchaExpirationGetter(): void
//    {
//        $captchaExpiration = time();
//
//        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);
//        $imageCaptchaService->method('getCaptchaExpiration')->willReturn($captchaExpiration);
//
//        $captchaService = $this->getSut($imageCaptchaService);
//
//        $this->assertSame($captchaExpiration, $captchaService->getCaptchaExpiration());
//    }

//    public function testCaptchaGenerate(): void
//    {
//        $captchaImage = uniqid();
//        $imageCaptchaService = $this->createStub(ImageCaptchaServiceInterface::class);
//
//        $captchaService = $this->getSut($imageCaptchaService);
//        $imageCaptchaService->method('generate')->willReturn($captchaImage);
//
//        $this->assertSame($captchaImage, $captchaService->generate());
//    }

//    public function getSut(
//        ImageCaptchaServiceInterface $captchaService = null,
//        HoneyPotCaptchaServiceInterface $honeyPotService = null,
//    ): CaptchaServiceInterface {
//        return new CaptchaService(
//            []
//        );
//        return new CaptchaService(
//            captchaService: $captchaService ?? $this->createStub(ImageCaptchaServiceInterface::class),
//            honeyPotService: $honeyPotService ?? $this->createStub(HoneyPotCaptchaServiceInterface::class),
//        );
//    }
}
