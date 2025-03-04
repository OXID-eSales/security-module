<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Service;

use DateTimeImmutable;
use DateInterval;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Builder\ImageCaptchaBuilderInterface;
use OxidEsales\SecurityModule\Captcha\Captcha\Image\Validator\ImageCaptchaValidatorInterface;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;

class ImageCaptchaService implements ImageCaptchaServiceInterface
{
    final public const CAPTCHA_NAME = 'image_captcha';
    final public const CAPTCHA_REQUEST_PARAMETER = 'captcha';
    final public const CAPTCHA_SESSION_PARAMETER = 'captcha';
    final public const CAPTCHA_LIFETIME_SESSION_PARAMETER = 'captcha_expiration';

    public function __construct(
        private readonly ImageCaptchaBuilderInterface $captchaBuilder,
        private readonly ImageCaptchaValidatorInterface $captchaValidator,
        private readonly ModuleSettingsServiceInterface $settingsService,
        private readonly Session $session
    ) {
    }

    public function getName(): string
    {
        return self::CAPTCHA_NAME;
    }

    public function isEnabled(): bool
    {
        return $this->settingsService->isCaptchaEnabled();
    }

    public function getCaptcha(): string
    {
        return $this->session->getVariable(self::CAPTCHA_SESSION_PARAMETER) ?: '';
    }

    public function getCaptchaExpiration(): int
    {
        return $this->session->getVariable(self::CAPTCHA_LIFETIME_SESSION_PARAMETER) ?: 0;
    }

    /**
     * @param Request $request
     * @return void
     */
    public function validate(Request $request): void
    {
        $this->captchaValidator->validate(
            $request->getRequestParameter(self::CAPTCHA_REQUEST_PARAMETER, ''),
            $this->getCaptcha(),
            $this->getCaptchaExpiration()
        );
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        $this->session->setVariable(self::CAPTCHA_SESSION_PARAMETER, $this->captchaBuilder->getContent());

        $time = new DateTimeImmutable('now');
        $expireTime = $time->add(
            new DateInterval('PT' . $this->settingsService->getCaptchaLifeTime())
        );
        $this->session->setVariable(self::CAPTCHA_LIFETIME_SESSION_PARAMETER, $expireTime->getTimestamp());

        return $this->captchaBuilder->build();
    }
}
