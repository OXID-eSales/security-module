<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\Service;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Session\SessionInterface;

readonly class InternalRedirectService implements InternalRedirectServiceInterface
{
    public const AUTH_REDIRECT_URL = 'otp_target_url';
    public function __construct(
        private SessionInterface $session,
        private Config $config,
    ) {
    }

    public function getRedirectUrl(): string
    {
        $storedUrl = $this->session->get(self::AUTH_REDIRECT_URL);

        if ($storedUrl && $this->isInternalUrl($storedUrl)) {
            return $storedUrl;
        }

        return $this->config->getShopHomeUrl();
    }

    private function isInternalUrl(string $url): bool
    {
        $shopUrl = $this->config->getShopUrl();
        $sslShopUrl = $this->config->getSslShopUrl();

        return str_starts_with($url, $shopUrl) || str_starts_with($url, $sslShopUrl);
    }
}
