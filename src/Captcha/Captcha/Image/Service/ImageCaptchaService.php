<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Service;

use DateTimeImmutable;
use DateInterval;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder\ImageCaptchaBuilderInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator\ImageCaptchaValidatorInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;

class ImageCaptchaService implements ImageCaptchaServiceInterface
{
    public function __construct(
        private readonly ImageCaptchaBuilderInterface $captchaBuilder,
        private readonly ImageCaptchaValidatorInterface $captchaValidator,
        private readonly ModuleSettingsServiceInterface $settingsService
    ) {
    }

    /**
     * @param string $userCaptcha
     * @param string $sessionCaptcha
     * @return void
     */
    public function validate(string $userCaptcha, string $sessionCaptcha): void
    {
        //todo: move after captcha validation refactoring
        $captchaExpireDate = (string) $_SESSION['captcha_expiration'];
        if (time() > $captchaExpireDate) {
            throw new CaptchaValidateException('ERROR_EXPIRED_CAPTCHA');
        }

        $this->captchaValidator->validate($userCaptcha, $sessionCaptcha);
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        $_SESSION['captcha'] = $this->captchaBuilder->getContent();

        $time = new DateTimeImmutable('now');
        $expireTime = $time->add(
            new DateInterval('PT' . $this->settingsService->getCaptchaLifeTime())
        );
        $_SESSION['captcha_expiration'] = $expireTime->getTimestamp();

        return $this->captchaBuilder->build();
    }
}
