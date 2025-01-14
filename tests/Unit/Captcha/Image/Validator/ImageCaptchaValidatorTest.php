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
        $validator->validate('');
    }

    public function testValidateWhenCaptchaIsInvalid(): void
    {
        $_SESSION['captcha'] = 'ABC123';

        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');

        $validator = $this->getSut();
        $validator->validate('WRONG123');
    }

    public function testValidateWhenCaptchaIsValid(): void
    {
        $_SESSION['captcha'] = 'ABC123';

        $validator = $this->getSut();
        $validator->validate('ABC123');
    }

    public function testValidateHandlesCaseSensitivity(): void
    {
        $_SESSION['captcha'] = 'AbC123';

        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');

        $validator = $this->getSut();
        $validator->validate('abc123');
    }

    public function testValidateWhenSessionCaptchaIsMissing(): void
    {
        unset($_SESSION['captcha']);

        $this->expectException(CaptchaValidateException::class);
        $this->expectExceptionMessage('ERROR_INVALID_CAPTCHA');

        $validator = $this->getSut();
        $validator->validate('abc123');
    }

    public function getSut(): ImageCaptchaValidatorInterface
    {
        return new ImageCaptchaValidator();
    }
}
