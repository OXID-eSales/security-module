<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Tests\Unit\Captcha\Image\Builder;

use OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder\ImageCaptchaBuilder;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder\ImageCaptchaBuilderInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\GDMethodsException;
use PHPUnit\Framework\TestCase;

class ImageCaptchaBuilderTest extends TestCase
{
    public function testDefaultConstructor()
    {
        $builder = $this->getSut();
        $content = $builder->getContent();

        $this->assertEquals(6, strlen($content));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]+$/', $content);
    }

    public function testCustomContent()
    {
        $builder = $this->getSut(content: $captchaText = substr(uniqid(), -6));
        $this->assertEquals($captchaText, $builder->getContent());
    }

    public function testBuildReturnsImageData()
    {
        $builder = $this->getSut();
        $imageData = $builder->build();

        $this->assertNotEmpty($imageData);
        $this->assertStringStartsWith("\xFF\xD8", $imageData);
    }

    public function testCreateImageThrowsExceptionWhenMethodsMissing()
    {
        $builder = $this->getMockBuilder(ImageCaptchaBuilder::class)
            ->onlyMethods(['createImage'])
            ->getMock();

        $builder->method('createImage')->will($this->throwException(new GDMethodsException()));

        $this->expectException(GDMethodsException::class);
        $builder->build();
    }

    public function getSut(
        ?string $content = null
    ): ImageCaptchaBuilderInterface {
        return new ImageCaptchaBuilder(
            content: $content
        );
    }
}
