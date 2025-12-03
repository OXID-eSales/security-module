<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Facebook;

use League\OAuth2\Client\Provider\Facebook as FacebookProvider;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;

class FacebookProviderFactory implements FacebookProviderFactoryInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSettings,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function create(): FacebookProvider
    {
        return new FacebookProvider([
            'clientId'        => $this->moduleSettings->getFacebookClientId(),
            'clientSecret'    => $this->moduleSettings->getFacebookClientSecret(),
            'redirectUri'     => $this->moduleSettings->getFacebookRedirectUrl(),
            'graphApiVersion' => 'v2.10',
        ]);
    }
}
