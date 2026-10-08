<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\EmailVerification\Service;

use OxidEsales\Eshop\Core\Config;

class VerificationUrlBuilder implements VerificationUrlBuilderInterface
{
    private const CONTROLLER = 'oesm_email_verification';

    public function __construct(
        private Config $config,
    ) {
    }

    public function buildVerificationLink(#[\SensitiveParameter] string $token): string
    {
        return $this->getPageUrl() . '&fnc=verifyEmail&token=' . $token;
    }

    public function buildPendingPageUrl(): string
    {
        return $this->getPageUrl() . '&state=pending';
    }

    private function getPageUrl(): string
    {
        return str_replace('&amp;', '&', $this->config->getShopSecureHomeUrl()) . 'cl=' . self::CONTROLLER;
    }
}
