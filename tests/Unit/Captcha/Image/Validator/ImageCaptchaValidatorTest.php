<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Image\Validator;

use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator\ImageCaptchaValidator;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator\ImageCaptchaValidatorInterface;
use PHPUnit\Framework\TestCase;

class ImageCaptchaValidatorTest extends TestCase
{
    public function testValidateWhenCaptchaIsEmpty(): void
    {
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_EMPTY_CAPTCHA');

        $validator = $this->getSut();
        $validator->validate('', uniqid());
    }

    public function testValidateWhenCaptchaIsInvalid(): void
    {
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');

        $validator = $this->getSut();
        $validator->validate(uniqid(), uniqid());
    }

    public function testValidateWhenCaptchaIsValid(): void
    {
        $sameCaptchaText = uniqid();
        $validator = $this->getSut();
        $validator->validate($sameCaptchaText, $sameCaptchaText);
    }

    public function testValidateHandlesCaseSensitivity(): void
    {
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');

        $validator = $this->getSut();
        $validator->validate('abc123', 'AbC123');
    }

    public function testValidateWhenSessionCaptchaIsMissing(): void
    {
        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');

        $validator = $this->getSut();
        $validator->validate(uniqid(), '');
    }

    public function getSut(): ImageCaptchaValidatorInterface
    {
        return new ImageCaptchaValidator();
    }
}
