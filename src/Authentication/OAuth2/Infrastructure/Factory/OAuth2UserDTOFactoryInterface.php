<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory;

use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;

interface OAuth2UserDTOFactoryInterface
{
    public function createFromFacebookUser(FacebookUser $facebookUser): OAuth2UserDTOInterface;

    public function createFromGoogleUser(GoogleUser $googleUser): OAuth2UserDTOInterface;
}
