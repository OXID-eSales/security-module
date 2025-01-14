<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder;

use GdImage;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaGenerateException;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\GDLibraryMissingException;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\GDMethodsMissingException;

class ImageCaptchaBuilder implements ImageCaptchaBuilderInterface
{
    public function __construct(
        private int $lenght = 6,
        private int $imageWidth = 120,
        private int $imageHeight = 40,
        private string $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
        private ?string $content = null
    ) {
        $this->content = is_string($content) ? $content : $this->buildCaptchaContent();
    }

    /**
     * @return string
     */
    protected function buildCaptchaContent(): string
    {
        return substr(str_shuffle($this->characters), 0, $this->lenght);
    }

    /**
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return string
     * @throws CaptchaGenerateException
     * @throws GDLibraryMissingException
     * @throws GDMethodsMissingException
     */
    public function build(): string
    {
        if (!extension_loaded('gd')) {
            throw new GDLibraryMissingException();
        }

        $image = $this->createImage();
        if (!$image) {
            throw new CaptchaGenerateException();
        }

        $this->addNoise($image);
        $this->addText($image);
        $this->addBorder($image);

        return $this->outputImage($image);
    }

    /**
     * @return GdImage|false
     * @throws GDMethodsMissingException
     */
    protected function createImage(): GdImage|false
    {
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
            throw new GDMethodsMissingException();
        }

        $image = imagecreatetruecolor($this->imageWidth, $this->imageHeight);
        $background = (int) imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $background);

        return $image;
    }

    /**
     * @param GdImage $image
     * @return void
     */
    private function addNoise(GdImage $image): void
    {
        $noiseColor = (int) imagecolorallocate($image, 100, 100, 100);

        // Add random lines
        for ($i = 0; $i < 10; $i++) {
            imageline(
                $image,
                rand(0, $this->imageWidth),
                rand(0, $this->imageHeight),
                rand(0, $this->imageWidth),
                rand(0, $this->imageHeight),
                $noiseColor
            );
        }

        // Add random dots
        for ($i = 0; $i < 500; $i++) {
            imagesetpixel($image, rand(0, $this->imageWidth), rand(0, $this->imageHeight), $noiseColor);
        }
    }

    /**
     * @param GdImage $image
     * @return void
     */
    private function addText(GdImage $image): void
    {
        $textColor = (int) imagecolorallocate($image, 0, 0, 0); // Black
        $offsetX = 10;
        $fontSize = 5;

        $characters = str_split((string) $this->content);

        foreach ($characters as $char) {
            // Add random vertical offset for distortion
            $offsetY = rand($this->imageHeight / 4, $this->imageHeight / 2);

            // Draw each character
            imagestring($image, $fontSize, $offsetX, $offsetY, $char, $textColor);

            $offsetX += rand(10, 20); // Add spacing
        }
    }

    /**
     * @param GdImage $image
     * @return void
     */
    private function addBorder(GdImage $image): void
    {
        $borderColor = (int) imagecolorallocate($image, 0, 0, 0);
        imagerectangle($image, 0, 0, $this->imageWidth - 1, $this->imageHeight - 1, $borderColor);
    }

    /**
     * @param GdImage $image
     * @return string
     * @throws CaptchaGenerateException
     */
    private function outputImage(GdImage $image): string
    {
        ob_start();
        $result = imagejpeg($image, null, 90);
        $imageData = ob_get_clean();

        imagedestroy($image);

        if (!$result || !$imageData) {
            throw new CaptchaGenerateException();
        }

        return $imageData;
    }
}
