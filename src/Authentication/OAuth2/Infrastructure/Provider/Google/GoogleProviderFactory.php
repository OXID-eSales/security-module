<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Provider\Google;

use League\OAuth2\Client\Provider\Google as GoogleProvider;
use OxidEsales\SecurityModule\Authentication\OAuth2\Service\ModuleSettingsServiceInterface;

class GoogleProviderFactory implements GoogleProviderFactoryInterface
{
    public function __construct(
        private readonly ModuleSettingsServiceInterface $moduleSettings,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function create(): GoogleProvider
    {
        return new GoogleProvider([
            'clientId'        => $this->moduleSettings->getGoogleClientId(),
            'clientSecret'    => $this->moduleSettings->getFacebookClientSecret(),
            'redirectUri'     => $this->moduleSettings->getGoogleRedirectUrl(),
        ]);
    }
}
