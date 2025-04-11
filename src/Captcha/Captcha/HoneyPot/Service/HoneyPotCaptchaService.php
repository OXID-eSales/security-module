<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Service;

use OxidEsales\Eshop\Core\Request;
use OxidEsales\SecurityModule\Captcha\Captcha\HoneyPot\Exception\CaptchaValidateException;
use OxidEsales\SecurityModule\Captcha\Service\ModuleSettingsServiceInterface;
use Psr\Log\LoggerInterface;

class HoneyPotCaptchaService implements HoneyPotCaptchaServiceInterface
{
    final public const CAPTCHA_NAME = 'honey_pot';

    final public const CAPTCHA_REQUEST_PARAMETER = 'lastname_confirm';

    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSetting,
        protected LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return self::CAPTCHA_NAME;
    }

    public function isEnabled(): bool
    {
        return $this->moduleSetting->isHoneyPotCaptchaEnabled();
    }

    public function validate(Request $request): void
    {
        $value = $request->getRequestParameter(self::CAPTCHA_REQUEST_PARAMETER);
        if (!$value) {
            return;
        }

        if (strlen($value) > 1) {
            $this->logger->debug('HONEYPOT_VALIDATION_FAILED');
            throw new CaptchaValidateException('FORM_VALIDATION_FAILED');
        }
    }

    public function generate(): string
    {
        return '';
    }
}
