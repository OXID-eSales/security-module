<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Authentication\OAuth2\Infrastructure\Factory;

use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTO;
use OxidEsales\SecurityModule\Authentication\OAuth2\DTO\OAuth2UserDTOInterface;

class OAuth2UserDTOFactory implements OAuth2UserDTOFactoryInterface
{
    public function createFromFacebookUser(FacebookUser $facebookUser): OAuth2UserDTOInterface
    {
        return new OAuth2UserDTO(
            $facebookUser->getFirstName(),
            $facebookUser->getLastName(),
            $facebookUser->getEmail(),
        );
    }

    public function createFromGoogleUser(GoogleUser $googleUser): OAuth2UserDTOInterface
    {
        return new OAuth2UserDTO(
            $googleUser->getFirstName(),
            $googleUser->getLastName(),
            $googleUser->getEmail(),
        );
    }
}
