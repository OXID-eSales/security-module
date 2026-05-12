<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Captcha\Image\Service;

use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\CaptchaAudioService;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Service\ImageCaptchaServiceInterface;
use OxidEsales\SecurityModule\Captcha\Infrastructure\LanguageWrapperInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CaptchaAudioServiceTest extends TestCase
{
    #[DataProvider('dataProviderLanguage')]
    public function testGenerateAudio(string $language)
    {
        $captchaServiceStub = $this->createStub(ImageCaptchaServiceInterface::class);
        $captchaServiceStub->method('getCaptcha')->willReturn('1234');

        $languageWrapperStub = $this->createStub(LanguageWrapperInterface::class);
        $languageWrapperStub->method('getCurrentLanguageAbbr')->willReturn($language);

        $captchaAudioService = $this->getSut($captchaServiceStub, $languageWrapperStub);

        $result = $captchaAudioService->generate();

        $this->assertIsString($result);
        $this->assertStringContainsString('RIFF', $result);
    }

    public function testGenerateAudioNoCaptcha()
    {
        $captchaServiceStub = $this->createStub(ImageCaptchaServiceInterface::class);
        $captchaServiceStub->method('getCaptcha')->willReturn('');

        $captchaAudioService = $this->getSut($captchaServiceStub);

        $result = $captchaAudioService->generate();

        $this->assertIsString($result);
        $this->assertStringNotContainsString('RIFF', $result);
    }

    public function getSut(
        ImageCaptchaServiceInterface $imageCaptchaService = null,
        LanguageWrapperInterface $languageWrapper = null,
    ): CaptchaAudioService {
        return new CaptchaAudioService(
            imageCaptchaService: $imageCaptchaService ?? $this->createStub(ImageCaptchaServiceInterface::class),
            language: $languageWrapper ?? $this->createStub(LanguageWrapperInterface::class),
        );
    }

    public static function dataProviderLanguage(): iterable
    {
        yield ['en'];
        yield ['de'];
        yield ['smth'];
        yield [''];
    }
}
