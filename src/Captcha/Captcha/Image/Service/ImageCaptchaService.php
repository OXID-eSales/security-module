<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Service;

use DateTimeImmutable;
use DateInterval;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder\ImageCaptchaBuilderInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator\ImageCaptchaValidatorInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;

class ImageCaptchaService implements ImageCaptchaServiceInterface
{
    public function __construct(
        private readonly ImageCaptchaBuilderInterface $captchaBuilder,
        private readonly ImageCaptchaValidatorInterface $captchaValidator,
        private readonly ModuleSettingsServiceInterface $settingsService,
        private readonly Session $session
    ) {
    }

    public function getCaptcha(): string
    {
        return $this->session->getVariable('captcha');
    }

    public function getCaptchaExpiration(): int
    {
        return $this->session->getVariable('captcha_expiration');
    }

    /**
     * @param string $userCaptcha
     * @param string $sessionCaptcha
     * @return void
     */
    public function validate(string $userCaptcha, string $sessionCaptcha): void
    {
        $this->captchaValidator->validate(
            $userCaptcha,
            $sessionCaptcha,
            $this->getCaptchaExpiration()
        );
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        $this->session->setVariable('captcha', $this->captchaBuilder->getContent());

        $time = new DateTimeImmutable('now');
        $expireTime = $time->add(
            new DateInterval('PT' . $this->settingsService->getCaptchaLifeTime())
        );
        $this->session->setVariable('captcha_expiration', $expireTime->getTimestamp());

        return $this->captchaBuilder->build();
    }
}
